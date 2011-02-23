<?php
/**
 * @package WordPress
 * @subpackage Blueprint_Theme
 */

$debug = true;	// Debug mode will add an extra module to the bottom of the page

include_once("header.php");
include_once("functions.php");

// ----------- Limit the number of posts per page if the device isn't A-grade -----------
$x_client_misc = get_header_request('HTTP_X_CLIENT_MISC');
$x_client_misc   = bp_header_explode ($x_client_misc);
$browser_grade = $x_client_misc['browser_grade'];  
if ($browser_grade == "A") {
	$posts_per_page = 5;
} else {
	$posts_per_page = 1;
}

$paged = $_GET['paged'];
$blog_title = get_bloginfo();

?>
<page style="collection">
  <models>
    <model id="my_search">
	  <instance id="search_query">
	    <data xmlns="">
	      <search></search>
	    </data>
	  </instance>
      <submission method="urlencoded-post" resource="..." />
    </model>
  </models>
  <page-header>
    <page-title><?php echo $blog_title; ?></page-title>
  </page-header>
  <content>
<?php

// Start the Wordpress loop
if (have_posts()) :
	query_posts('paged=' . $paged . '&showposts=' . $posts_per_page);		//limit the number of posts displayed
	
	while (have_posts()) : the_post();
		// Get the post-specific data from Wordpress
		$post_title           = get_the_title();
		$post_content         = get_the_content();
		$post_content         = bp_html_to_bp($post_content);
		$post_author          = get_the_author();
		$post_time            = get_the_time('F jS, Y');
		//$post_permalink = get_the_permalink();
		$post_ID        	  = get_the_ID();
		$post_comments_number = (int) get_comments_number();
		$post_tags            = "";
		$post_tags_array      = get_the_tags();
		if ( $post_tags_array ) {
			foreach($post_tags_array as $tag) {
				$post_tags .= $tag->name . " ";
			}
		}
		if ($post_tags) {
			$post_tags = "<block>Tags: " . $post_tags . "</block>";
		}
		
		if ($post_comments_number > 0) {
			$comments_link = <<< BLUEPRINT
    <placard class="link">
      <layout-items>
        <block> 
	      <inline-trigger>
	        <label>$post_comments_number comments</label>
	        <load event="activate" resource="comments-view.php?id=$post_ID" />
	      </inline-trigger>
        </block>
      </layout-items>
      <load event="activate" resource="comments-view.php?id=$post_ID" />
    </placard>	
BLUEPRINT;
		}
		
		echo <<< BLUEPRINT
    <module>
      <header layout="simple">
        <layout-items>
	      <block class="title">$post_title</block>
  	    </layout-items>
      </header>
      <block class="subtext">Posted by $post_author on $post_time</block>
      <block class="description">
         $post_content
      </block>
      $post_tags
      $comments_link
      <trigger>
        <label>Leave a Comment</label>
        <load-page event="activate" page="comment.php?id=$post_ID" />
      </trigger>
      <block>
        <br/>
      </block>
    </module>
BLUEPRINT;
		
	endwhile;
	
	$back = get_next_posts_link('Older Posts');
	$back = bp_a_to_bp ($back, "      ", false);
	
	$forward = get_previous_posts_link('Newer Posts');
	$forward = bp_a_to_bp ($forward, "      ", false);

	// Navigation
	echo <<< BLUEPRINT
    <module>
      <block>
	    $back
	    $forward
      </block>
    </module>
BLUEPRINT;
	
else :
// No matching posts
		echo <<< BLUEPRINT
    <module>
      <header layout="simple">
        <layout-items>
	      <block class="title">No matching posts</block>
	    </layout-items>
      </header>
      <block>Sorry, no posts matched your criteria.</block>
      <block>
        <br/>
      </block>
    </module>
BLUEPRINT;
endif;

?>

    <module>
      <placard class="callout subdued">
        <layout-items>
          <block class="title">Search <?php echo $blog_title; ?></block>
        </layout-items>
      </placard>
      <input model="my_search" ref="search">
      </input>
      <submit>
        <label>Search</label>
      </submit>
    </module>
  <?php if ($debug) echo bp_get_debug(); ?>
  <?php echo bp_get_footer(); ?>

  </content>
</page>