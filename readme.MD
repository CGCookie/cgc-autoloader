# CGC Autoloader
This is kind of like a PSR0 autolaoder, with some CGC assumptions built into it.


## How It Works
### Class Name => File Name Pattern
For CGC Core:

* cgcOne => class.one.php
* cgcOneTwo => class.one-two.php
* cgcOneTwoThree => class.one-two-three.php

BTW, all caps acronyms are not your friend. For example, `cgcEDD` doesn't work `cgcEdd` does.


For other plugins:

* cgc{namespace}One => class.one.php
* cgc{namespace}OneTwo => class.one-two.php
* cgc{namespace}OneTwoThree => class.one-two-three.php

Note: Location is figured out programmaticly if using the normal `/includes`, `/public/includes` or `/admin/includes` locations. Else, add class_name => file_path explicitly using `cgcAutoload->set_special()`


### Hooks


### API Endpoints
Any class that impliments the `cgcApiInterface` will have a REST API route added for it automagically. Inline docs for that interface document those requirements.

### Add New Plugins To This
1. Move the path constant to the main mu-plugin (you will need to use `WP_PLUGIN_DIR` for path.
2. Add namespace and file path to ``cgcAutoload->namespace`


## Questions?
This autoloader was designed by Josh Pollock. Ask questions in Slack (Josh) or email (Josh@JoshPress.net).
