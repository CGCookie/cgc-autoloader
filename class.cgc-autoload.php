<?php

/**
 * Class cgcAutoload
 *
 * Autoloader for CGC
 *
 * @since 5.7
 */
class cgcAutoload {

	/**
	 * An array of special case class files
	 *
	 * 'class_name' => 'file_path'
	 *
	 * @since     6.0.0
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $special_classes;

	/**
	 * An array of class files from the public includes dir
	 *
	 * @since     6.0.0
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $public_files;

	/**
	 * An array of class files from the admin includes dir
	 *
	 * @since     6.0.0
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $admin_files;

	/**
	 * An array of class files from the root includes dir
	 *
	 * @since     6.0.0
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $inc_files;

	private $namespaces = array(
		'core' => CGC5_CORE_DIR,
		'video' => CGC_VIDEO_TRACKING_DIR,
	);

	/**
	 * Instance of this class.
	 *
	 * @since     6.0.0
	 *
	 * @access private
	 *
	 * @var      object|cgcAutoload
	 */
	private static $instance = null;




	/**
	 * Constructor
	 *
	 * @since     6.0.0
	 */
	private function __construct() {
		$this->set_special();
		$this->set_files();


	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     6.0.0
	 *
	 * @return    object|cgcAutoload    A single instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Attempt to autoload a class, and do so if is CGC
	 *
	 * @param string $class Name of the class
	 *
	 * @throws \Exception
	 */
	public function maybe_load( $class ) {
		if( $this->is_special( $class ) ) {
			$full_path = $this->special_classes[ $class ];
			include_once( $full_path );
		}else{

			//$_class holds class name without anny futzing for later reference
			$_class  = $class;
			$class   = str_replace( array( 'cgc', 'Five' ), '', $class );
			$namespace = $this->deterimine_namespace( $class );
			if( 'core' != $namespace ) {
				$class = str_replace( $namespace, '', $class );
			}

			$matches = array();
			preg_match_all( '/[A-Z]/', $class, $matches, PREG_OFFSET_CAPTURE );
			$matches = $matches[0];
			$class   = strtolower( $class );

			if ( 0 == count( $matches ) ) {
				throw new Exception( 'cgc-autoloader Class not found' . $_class );

				return;

			} elseif ( 1 == count( $matches ) ) {
				$filename = 'class.' . $class . '.php';
			} elseif( 2 == count( $matches )) {

				$filename = 'class.' . substr_replace( $class, '-', $matches[1][1] ) . substr( $class, $matches[1][1] ) . '.php';
			}elseif( 3 === count( $matches ) ) {
				$parts[0] = substr( $class, $matches[0][1], 1 );
				$parts[1] = substr( $class, $matches[1][1], 1  );
				$parts[2] = substr( $class, $matches[2][1], 1 );

				$parts[0] = str_replace( $parts[1], '', $class );
				$parts[1] = str_replace( $parts[2], '', $parts[1] );
				$filename = 'class.' . implode( '-', $parts ) . '.php';
			}else{
				$filename = false;
			}

			if( $filename ){
				$path = $this->namespaces[ $namespace ];

				$full_path = trailingslashit( $path ) . 'public/includes/' . $filename;
				if ( in_array( $full_path, $this->public_files[ $namespace ] ) ) {
					include_once( $full_path );
				} else {

					$full_path =  trailingslashit( $path ) . 'admin/includes/' . $filename;

					if ( in_array( $full_path, $this->admin_files[ $namespace ] ) ) {
						include_once( $full_path );
					}else{
						$full_path = trailingslashit( $path ) . 'includes/' . $filename;
						if( in_array( $full_path, $this->inc_files[ $namespace ] ) ) {
							include_once( $full_path );
						}else{

							throw new Exception( 'cgc-autoloader Class not found' . $_class );

							return;

						}

					}

				}

				$this->maybe_make_route( $_class );

			}

		}

	}

	/**
	 * Determines if the class is a registered namespace, if not assumes core, which may not be the case, but that's OK.
	 *
	 * Must run after we strip cgc and five from class name or will not work :(
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	protected function deterimine_namespace( $class ) {
		foreach( $this->namespaces as $namespace => $path ) {
			if( $namespace == substr( $class, 0, strlen( $namespace ) ) ) {
				return $namespace;
			}

		}

		return 'core';

	}

	/**
	 * Check if class being loaded is special.
	 *
	 * @since 6.0.0
	 *
	 * @access protected
	 *
	 * @param string $class Name of the class
	 *
	 * @return bool
	 */
	protected function is_special( $class ) {
		if( array_key_exists( $class, $this->special_classes ) ) {
			return true;
		}

	}

	/**
	 * Checks if class needs an API route and makes it.
	 *
	 * @since 6.0.0
	 *
	 * @access protected
	 *
	 * @param string $class Name of the class
	 */
	protected function maybe_make_route( $class ) {
		$impliments = class_implements( $class );
		if( ! empty( $impliments ) && in_array( 'cgcApiInterface', $impliments ) ) {
			cgcRouteFactory::create( $class );
		}

	}

	/**
	 * Generic glober for PHP files in a path
	 *
	 * @param string $path Absolute path.
	 *
	 * @return array Array of PHP files in path
	 */
	protected function glob( $path ) {
		return glob(  $path . '/*.php' );

	}

	/**
	 * Set the inc_files, public_files and admin_files properties
	 *
	 * @since 6.0.0
	 *
	 * @access private
	 */
	private function set_files() {
		foreach( $this->namespaces as $psuedonamespace => $path ) {
			$this->inc_files[ $psuedonamespace ] = $this->glob( $path . '/includes' );
			$this->admin_files[ $psuedonamespace ] = $this->glob( $path . '/admin/includes' );
			$this->public_files[ $psuedonamespace ] = $this->glob( $path . '/public/includes' );
		}

	}

	/**
	 * Set the special_classes property
	 *
	 * @since 6.0.0
	 *
	 * @access private
	 */
	private function set_special() {
		$this->special_classes = array(
			'CGC_Core' => trailingslashit( CGC5_CORE_DIR ) . 'public/class-cgc-core.php',
			'CGC_Core_Admin' => trailingslashit( CGC5_CORE_DIR ) . 'admin/class-cgc-core-admin.php'
		);

	}


}
