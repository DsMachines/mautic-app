$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

// CPaaS Debug Control - Set to false in production
define('CPAAS_DEBUG', true);



/**
* For developers: WordPress debugging mode.
*
* Change this to true to enable the display of notices during development.
* It is strongly recommended that plugin and theme developers use WP_DEBUG
* in their development environments.
*
* For information on other constants that can be used for debugging,
* visit the documentation.
*
* @link https://wordpress.org/support/article/debugging-in-wordpress/
*/
if ( ! defined( 'WP_DEBUG' ) ) {
define( 'WP_DEBUG', true );
// Enhanced debug logging (added by Dashboard plugin)
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );
} 