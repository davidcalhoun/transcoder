<?php
/**
 * @package WordPress
 * @subpackage Blueprint_Theme
 */

include_once("header.php");

$blog_title = get_bloginfo();							// Get the blog title from Wordpress
if ( $_GET['id'] ) {
	$post_ID = (int) $_GET['id'];
} elseif ( $_POST['post-ID'] ) {
	$post_ID = (int) $_POST['post-ID'];
}

$post_title = get_the_title( $post_ID );

if (! $_POST['author'] || ! $_POST['comment'] ) :

?>
<page style="collection">
  <models>
    <model>
      <instance>
        <data xmlns="">
	      <author />
		  <email></email>
          <comment />
          <post-ID><?php echo $post_ID; ?></post-ID>
        </data>
      </instance>
      <submission method="urlencoded-post" resource="comment.php" />
    </model>
  </models>
  <page-header>
    <title-bar>
      <title><?php echo $blog_title; ?></title>
    </title-bar>
  </page-header>
  <content>
    <module>
      <placard class="callout subdued">
        <layout-items>
          <block class="title">Post a comment on "<?php echo $post_title; ?>"</block>
        </layout-items>
      </placard>
      <input ref="author">
        <label>Name</label>
      </input>
	  <input ref="email">
	    <label>Email</label>
	  </input>
      <textarea ref="comment">
      	<label>Comment</label>
	  </textarea>
      <submit>
        <label>Submit</label>
      </submit>
    </module>
  </content>
</page>
<?php
else :
// Comment has been posted, so process it

$comment_post_ID      = (int) $_POST['post-ID'];
$comment_author       = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : null;
$comment_author_email = ( isset($_POST['email']) )   ? trim($_POST['email']) : null;
$comment_author_url   = ( isset($_POST['url']) )     ? trim($_POST['url']) : null;
$comment_content      = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : null;
$comment_type         = '';
$comment_parent       = isset($_POST['comment_parent']) ? absint($_POST['comment_parent']) : 0;
$user 				  = $user = wp_get_current_user();
$user_ID              = isset($user->ID) ? (int) $user->ID : null;

//TODO: error-checking: check if post exists, comments are open, etc. (check wp-comments.post.php)

$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

$comment_id = wp_new_comment( $commentdata );

?>
<page style="collection">
  <page-header>
    <title-bar>
      <title><?php echo $blog_title; ?></title>
    </title-bar>
  </page-header>
  <content>
    <module>
	  <block>Your comment has been posted to "<?php echo $post_title; ?>"!</block>
	  <block>Post ID: <?php echo $comment_post_ID; ?></block>
      <block>Author: <?php echo $comment_author; ?></block>
      <block>Email: <?php echo $comment_author_email; ?></block>
      <block>Comment: <?php echo $comment_content; ?></block>
    </module>
  </content>
</page>

<?php
endif;
?>