<?php
/**
 * @package WordPress
 * @subpackage Blueprint_Theme
 */

// ----------- Blueprint Helper functions -----------

function get_header_request ($header_name) {
	// Gets the header request of the current page, without making an
	// extra request like get_headers() appears to do.
	$header_value = stripslashes ($_SERVER[$header_name]);
	
	return $header_value;
}

function bp_header_explode ($value) {
	$value = str_replace (";", "&", $value);
	$value = str_replace ("\"", "", $value);
	$value = parse_str ($value, $output);
	return $output;
}

function bp_get_browser_grade () {
	$http_x_client_misc = get_header_request ("HTTP_X_CLIENT_MISC");
	$http_x_client_misc = bp_header_explode ($http_x_client_misc);
	
	$browser_grade = $http_x_client_misc['browser_grade'];
	
	return $browser_grade;
}

function bp_get_user_agent () {
	return stripslashes (get_header_request ("HTTP_X_DEVICE_USER_AGENT"));
}

function bp_a_to_bp ($input, $indent = "    ", $full_path = true) {
	// Not yet fully functional
	
	// Returns an <inline-trigger>
	
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
	if (preg_match_all ("/$regexp/siU", $input, $matches)) {
		// $matches[2] = array of link addresses, $matches[3] = array of link text
		$address = $matches[2][0];
		$text    = $matches[3][0];
	}
	
	if (! $path_part) {
		$address = basename($address);
	}

	$output = <<< BLUEPRINT
$indent<inline-trigger>
$indent  <label>$text</label>
$indent  <load event="activate" resource="$address" />
$indent</inline-trigger>
BLUEPRINT;
	
	return $output;
}

function bp_html_to_bp ($input, $indent = "    ") {
	// Strip all HTML tags for now
	// TODO: convert HTML TO Blueprint format
	
	//$output = bp_a_to_bp ($html);

	$output = strip_tags($input);

	return $output;
}

function bp_get_debug () {
	// Get all the headers
	
	$accept = get_header_request ('ACCEPT');
	$accept_language = get_header_request ('ACCEPT_LANGUAGE');
	$geo_country_2 = get_header_request ('GEO_COUNTRY');
	$geo_position = get_header_request ('GEO_POSITION');
	$location = get_header_request ('LOCATION');
	$user_agent = get_header_request ('USER_AGENT');
	
	$x_carrier_info  = get_header_request ('HTTP_X_CARRIER_INFO');
	$x_carrier_info  = bp_header_explode ($x_carrier_info);
	$carrier_id      = $x_carrier_info['id'];
	$carrier_name    = $x_carrier_info['name'];
	$carrier_country = $x_carrier_info['country'];
	
	$x_client_accept   = get_header_request ('HTTP_X_CLIENT_ACCEPT');
	$video_support     = (strstr ($x_client_accept, "video")) ? 'true' : 'false';
	$image_support     = (strstr ($x_client_accept, "image")) ? 'true' : 'false';
	$blueprint_support = (strstr ($x_client_accept, "blueprint")) ? 'true' : 'false';
	
	$x_client_info   = get_header_request ('HTTP_X_CLIENT_INFO');
	$x_client_info   = bp_header_explode ($x_client_info);
	$client_vendor   = $x_client_info['vendor'];
	$client_model    = $x_client_info['model'];
	$client_version  = $x_client_info['version'];
	
	$x_client_misc   = get_header_request ('HTTP_X_CLIENT_MISC');
	$image_list_size = $x_client_misc['image_list_size'];
	$browser_grade   = $x_client_misc['browser_grade'];
	$screen_class    = $x_client_misc['screen_class'];
	
	$x_device_accept           = get_header_request ('HTTP_X_DEVICE_ACCEPT');
	$device_supports_xhtml_mp  = (strstr ($x_client_accept, "text/xhtml-mp")) ? 'true' : 'false';
	$device_supports_xml       = (strstr ($x_client_accept, "application/xml")) ? 'true' : 'false';
	$device_supports_xhtml_xml = (strstr ($x_client_accept, "application/xhtml+xml")) ? 'true' : 'false';
	$device_supports_html      = (strstr ($x_client_accept, "text/html")) ? 'true' : 'false';
	$device_supports_plain     = (strstr ($x_client_accept, "text/plain")) ? 'true' : 'false';
	$device_supports_png       = (strstr ($x_client_accept, "image/png")) ? 'true' : 'false';
	
	$x_device_info     = get_header_request ('HTTP_X_DEVICE_INFO');
	$x_device_info     = bp_header_explode ($x_device_info);
	$device_id         = $x_device_info['id'];
	$device_make       = $x_device_info['make'];
	$device_model      = $x_device_info['model'];
	$device_os         = $x_device_info['os'];
	$device_osver      = $x_device_info['osver'];
	$device_resolution = $x_device_info['resolution'];
	
	$device_user_agent_string = get_header_request ('HTTP_X_DEVICE_USER_AGENT');
	$x_forwarded_for = get_header_request ('HTTP_X_FORWARDED_FOR');
	$x_forwarded_host = get_header_request ('HTTP_X_FORWARDED_HOST');
	
	$x_geo_location = get_header_request ('HTTP_X_GEO_LOCATION');
	$x_geo_location = bp_header_explode ($x_geo_location);
	$geo_name       = $x_geo_location['name'];
	$geo_street     = $x_geo_location['street'];
	$geo_city       = $x_geo_location['city'];
	$geo_state      = $x_geo_location['state'];
	$geo_postal     = $x_geo_location['postal'];
	$geo_country    = $x_geo_location['country'];
	
	$x_request_id = get_header_request ('HTTP_X_REQUEST_ID');
	
	// Server data
	$phpversion = phpversion();

	// Prepare the output
	
	$device_output = "Device: ";
	if ($device_make)       $device_output .= $device_make . " ";
	if ($device_model)      $device_output .= $device_model . ", ";
	if ($device_id)         $device_output .= "ID: " . $device_id . ", ";
	if ($device_os)         $device_output .= "OS: " . $device_os . " ";
	if ($device_osver)      $device_output .= $device_osver . ", ";
	if ($device_resolution) $device_output .= "Screen: " . $device_resolution;
	
	$geo_output = "Geo data: ";
	if ($geo_position) $geo_output .= "Position: " . $geo_position . ", ";
	if ($geo_city)     $geo_output .= "City: " . $geo_city . ", ";
	if ($geo_state)    $geo_output .= "State: " . $geo_state . ", ";
	if ($geo_country)  $geo_output .= "Country: " . $geo_country . ", ";
	if ($geo_postal)   $geo_output .= "Postal/ZIP: " . $geo_postal;
	
	$carrier_output = "Carrier: ";
	if ($carrier_name) $carrier_output .= "Name: " . $carrier_name . ", ";
	if ($carrier_id) $carrier_output .= "ID: " . $carrier_id . ", ";
	if ($carrier_country) $carrier_output .= "Country: " . $carrier_country . "";
	
	$server_output = "Server info: ";
	if ($phpversion) $server_output .= "PHP Version: " . $phpversion . ", ";
	
	// Assemble the output
	
	$output = <<< BLUEPRINT
    <module class="featured">
      <header>
        <layout-items>
          <image size="small" resource="http://l.yimg.com/us.yimg.com/i/nt/ic/ut/bsc/info28_1.gif" />
          <block class="title">Debug</block>
        </layout-items>
      </header>
      <block class="subtext">
        $device_output
      </block>
	  <block class="subtext">
	    Browser grade: $browser_grade
      </block>
      <block class="subtext">
        $geo_output
      </block>
      <block class="subtext">
        $carrier_output
      </block>
      <block class="subtext">
        User agent string: $device_user_agent_string
      </block>
      <block class="subtext">
        $server_output
      </block>
    </module>	
BLUEPRINT;

	return $output;
}

function bp_get_footer () {
	
	?>
    <placard class="simple">
	  <layout-items>
        <block> 
	        <image size="small" resource="http://l.yimg.com/us.yimg.com/i/nt/ic/ut/bsc/ybang24_2.png" />
	        Powered by Yahoo! Blueprint and
        </block>
      </layout-items>
    </placard>
    <placard class="simple">
	  <layout-items>
        <block> 
	      <image size="small" resource="<?php echo ""; ?>wordpress-logo.png" />
	      Wordpress
        </block>
      </layout-items>
    </placard>
	
	<?php
}


?>
