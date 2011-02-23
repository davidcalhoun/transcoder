<?php
/**
 * @package WordPress
 * @subpackage Blueprint_Theme
 */

function blueprint_comment ($comment, $args, $depth) {
	//TODO: move this to functions.php
	
	$GLOBALS['comment'] = $comment;
	
	$comment_author = get_comment_author();
	$comment_time   = get_comment_time();
	$comment_text   = get_comment_text();
	
	echo <<< BLUEPRINT
	      <block>
			 $comment_author says: $comment_text ($comment_time).
	      </block>
BLUEPRINT;
}

// ----------- Blueprint Helper functions -----------

function get_header_request ($header_name) {
	// Gets the header request of the current page, without making an
	// extra request like get_headers() appears to do.
	
	$header_value = stripslashes ($_SERVER[$header_name]);
	
	return $header_value;
}

function bp_parse_headers () {
	foreach ($_SERVER as $header => $value) {
		$$header = $value;
	}
	
	
}

function bp_header_explode ($value) {
	$value = str_replace (";", "&", $value);
	$value = str_replace ("\"", "", $value);
	$value = parse_str ($value, $output);
	return $output;
}

function bp_get_browser_grade () {
	//TODO: refactor
	
	$x_client_misc = get_header_request ("HTTP_X_CLIENT_MISC");
	$grade = "";
	if (! $x_client_misc ) {
		return "";
	} elseif (strstr ($x_client_misc, "browser_grade=\"A\"")) {
		return "A";
	} elseif (strstr ($x_client_misc, "browser_grade=\"B\"")) {
		return "B";
	} elseif (strstr ($x_client_misc, "browser_grade=\"C\"")) {
		return "C";
	} elseif (strstr ($x_client_misc, "browser_grade=\"D\"")) {
		return "D";
	} elseif (strstr ($x_client_misc, "browser_grade=\"E\"")) {
		return "E";
	} elseif (strstr ($x_client_misc, "browser_grade=\"F\"")) {
		return "F";
	} else {
		return "";
	}
}

function bp_get_user_agent () {
	return stripslashes (get_header_request ("HTTP_X_DEVICE_USER_AGENT"));
}

function bp_html_to_bp ($html) {
	/*** a new dom object ***/
    $dom = new domDocument;

    /*** load the html into the object ***/
    $dom->loadHTML($html);

    /*** discard white space ***/
    $dom->preserveWhiteSpace = false;

    $links = $dom->getElementsByTagName('a');

	$html = "test";

	$html = $links->item(0)->nodeValue;
	$html = $links->item(0)->href;
	
	/*
	<a href=""></a>
	
	to
	
	<inline-trigger>
      <label>inline-trigger</label>
      <load event="activate" resource="#" />
    </inline-trigger>
    */

	return $html;
}



?>
