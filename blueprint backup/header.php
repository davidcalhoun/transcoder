<?php
/**
 * @package WordPress
 * @subpackage Classic_Theme
 */

require('../../../wp-blog-header.php');					// WP includes

header( "Content-Type: application/x-blueprint+xml" );  // Make sure we set the right content type
header( "Cache-Control: no-cache" );					// Make sure not to cache. The Java client really cares.


?>
