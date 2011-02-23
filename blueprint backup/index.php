<?php
/**
 * @package WordPress
 * @subpackage Blueprint_Theme
 */

/*
 * Woo, excessive documentation!
 * http://codex.wordpress.org/Template_Tags
*/

include_once("header.php");
include_once("functions.php");

$browser_grade = bp_get_browser_grade();
$user_agent    = bp_get_user_agent();
$debug = true;

// ----------- Limit the number of posts per page if the device isn't A-grade -----------
if ($browser_grade == "A") {
	$posts_per_page = 5;
} else {
	$posts_per_page = 1;
}

BP_parse_headers();

// ----------- Debug module -----------
if ($debug) {
	//$test = BP_header_explode (get_header_request ("HTTP_X_CLIENT_MISC"));
	//$test = $test['browser_grade'];
	
	$test = get_header_request ("HTTP_X_DEVICE_INFO");
	
	
	$debug_module = <<< BLUEPRINT
    <module class="featured">
      <header>
        <layout-items>
          <block class="title">Debug</block>
        </layout-items>
      </header>
	  <block class="subtext">
	    Browser grade: $browser_grade
      </block>
      <block class="subtext">
        User agent: $user_agent
      </block>
      <block>
        HTTP_X_DEVICE_INFO: $test
      </block>
	</module>	
BLUEPRINT;
} else {
	$debug_module = "";
}


$blog_title = get_bloginfo();							// Get the blog title from Wordpress

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
	query_posts('showposts=' . $BP_posts_per_page);		//limit the number of posts displayed
	
	while (have_posts()) : the_post();
		// Get the post-specific data from Wordpress
		$post_title           = get_the_title();
		$post_content         = get_the_content();
		$post_content         = bp_html_to_bp($post_content);
		$post_author          = get_the_author();
		$post_time            = get_the_time('F jS, Y');
		//$post_permalink = get_the_permalink();
		$post_ID        	  = get_the_ID();
		$post_comments_number = get_comments_number();
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
		
		echo <<< BLUEPRINT
  <module>
    <header layout="simple">
      <layout-items>
	    <block class="title">$post_title</block>
	  </layout-items>
    </header>
    <block class="subtext">Posted by $post_author on $post_time</block>
    <block class="description">$post_content</block>
    $post_tags
    <placard class="link">
      <layout-items>
        <block> 
	      <inline-trigger>
	        <label>$post_comments_number comments</label>
	        <load event="activate" resource="comments-view.php?id=$post_ID" />
	      </inline-trigger>
        </block>
      </layout-items>
      <load event="activate" resource="#" />
    </placard>
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
    <? echo $debug_module; ?>
  </content>
</page>