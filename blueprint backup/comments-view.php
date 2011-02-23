<?php
/**
 * @package WordPress
 * @subpackage Blueprint_Theme
 */

include_once("header.php");

$blog_title = get_bloginfo();							// Get the blog title from Wordpress
if ( $_GET['id'] ) {
	$post_ID = (int) $_GET['id'];
}

$post_title = get_the_title( $post_ID );

//TODO: pagination links

?>
<page style="collection">
  <page-header>
    <title-bar>
      <title><?php echo $blog_title; ?></title>
    </title-bar>
  </page-header>
  <content>
	<module>
      <block>Replies to "<?php echo $post_title; ?>"</block>
	</module>
	  <?php
	    // We have to use a custom mysql query here because the Wordpress comment loop is restricted to div, ol, and ul elements
	    $mysql_comment_rows = $wpdb->get_results( "SELECT * FROM wp_comments" ) or die ("<block>MySQL error.</block>");
		
		if ($mysql_comment_rows) {
			foreach ($mysql_comment_rows as $cur_comment) {
		        $comment_author       = $cur_comment->comment_author;
				$comment_author_email = $cur_comment->comment_author_email;
				$comment_date         = $cur_comment->comment_date;
				$comment_content      = $cur_comment->comment_content;
				$comment_approved     = $cur_comment->comment_approved;
			
				if ($comment_approved == "1") {
					// Comment is approved
				echo <<< BLUEPRINT
    <module>
	  <block class="title">
	    $comment_author on $comment_date
	  </block>
	  <block class="subtext">
	    $comment_content
	  </block>
	</module>
BLUEPRINT;
			    } else {
					// Comment not approved
				
			    }
		
		    }
    	}
	  ?>
  </content>
</page>