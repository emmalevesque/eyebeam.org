<?php
/*

Hello, this is the eyebeam2018 functions file.
(20180220/dphiffer)

For your convenience, here is a list of all the functions in here:
* eyebeam2018_acf_get_dir: make ACF work with symlinks
* eyebeam2018_setup: init hook
* eyebeam2018_enqueue_css: add cache-busting URL arg
* eyebeam2018_enqueue_js: add cache-busting URL arg
* eyebeam2018_enqueue: include front-end assets
* eyebeam2018_img_src: add cache-busting URL arg
* eyebeam2018_hero: register hero item
* eyebeam2018_module: register a module item
* eyebeam2018_render_heroes: calls get_template_part for each hero item
* eyebeam2018_render_modules: calls get_template_part for each hero item
* eyebeam2018_get_residents: returns array of resident posts
* eyebeam2018_ajax_residents: AJAX handler for residents-by-year
* eyebeam2018_video_embed: show an embed, given a video permalink
* eyebeam2018_youtube_embed: show a ~YouTube~ embed
* eyebeam2018_subscribe: AJAX handler for Mailchimp subscription
* eyebeam2018_subscribe_request: makes Mailchimp API request
* eyebeam2018_donate: AJAX handler for donations
* eyebeam2018_donate_request: makes Stripe API request
* eyebeam2018_donate_normalize: massage the donate submission values
* eyebeam2018_donate_validate: ensure the donate submission is valid
* eyebeam2018_extract_intro: the_content filter, pulls out intro text
* eyebeam2018_content_fields: inserts the_content-like ACF content fields
* eyebeam2018_shortcode_filter: tweak shortcode outputs
* eyebeam2018_view_source: show any secret blog posts
* eyebeam2018_view_source_post: register secret blog post
* dbug: kinda like error_log, but more flexible

*/


// We need this filters so that ACF can handle symlinked folders.
// (20180222/dphiffer)
function eyebeam2018_acf_get_dir($dir) {
	if (preg_match('#/wp-content/themes/.+$#', $dir, $matches)) {
		return $matches[0];
	}
	return $dir;
}
add_filter('acf/helpers/get_dir', 'eyebeam2018_acf_get_dir');

// Libraries
$dir = __DIR__;
include_once("$dir/lib/advanced-custom-fields/acf.php");
include_once("$dir/lib/acf-repeater/acf-repeater.php");
include_once("$dir/lib/custom-post-types.php");

// Enable WP_DEBUG in wp-config.php to edit fields
// WP_DEBUG = true / custom fields come from the database
// WP_DEBUG = false / custom fields are included via PHP
if (! defined('WP_DEBUG') || ! WP_DEBUG) {

	define('ACF_LITE', true); // hide the editing UI

	include_once("$dir/lib/custom-fields/archive-page.php");
	include_once("$dir/lib/custom-fields/archive-post.php");
	include_once("$dir/lib/custom-fields/board.php");
	include_once("$dir/lib/custom-fields/community.php");
	include_once("$dir/lib/custom-fields/events.php");
	include_once("$dir/lib/custom-fields/interns.php");
	include_once("$dir/lib/custom-fields/media-release.php");
	include_once("$dir/lib/custom-fields/modular-grid.php");
	include_once("$dir/lib/custom-fields/post.php");
	include_once("$dir/lib/custom-fields/recent-press.php");
	include_once("$dir/lib/custom-fields/residency.php");
	include_once("$dir/lib/custom-fields/residents.php");
	include_once("$dir/lib/custom-fields/staff.php");
}

function eyebeam2018_setup() {

	// Flip some WordPress switches to turn on features
	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');

	// Main navigation
	register_nav_menus(array(
		'top' => 'Top nav',
		'bottom' => 'Bottom nav'
	));

	// Don't show the version of WordPress (security, yo)
	remove_action('wp_head', 'wp_generator');

	// Weird that this is even necessary...
	add_theme_support('html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	));

	// Custom image sizes
	add_image_size('hero', 2560, 0, false);

	// Yeah, globals are bad, but at least we namespace ours
	$GLOBALS['eyebeam2018'] = array(
		'heroes' => array(),
		'modules' => array()
	);
}
add_action('init', 'eyebeam2018_setup');

// Add a cache-buster to the URL
function eyebeam2018_enqueue_css($path, $deps = array()) {
	$name = 'eyebeam2018-' . str_replace('/[^a-z0-9-]/', '-', $path);
	$url = get_stylesheet_directory_uri() . "/$path";
	$version = filemtime(__DIR__ . "/$path");
	wp_enqueue_style($name, $url, $deps, $version);
}

// Add a cache-buster to the URL
function eyebeam2018_enqueue_js($path, $deps = array(), $bottom = true) {
	$name = 'eyebeam2018-' . str_replace('/[^a-z0-9-]/', '-', $path);
	$url = get_stylesheet_directory_uri() . "/$path";
	$version = filemtime(__DIR__ . "/$path");
	wp_enqueue_script($name, $url, $deps, $version, $bottom);
}

// Add our CSS and JavaScript tags
function eyebeam2018_enqueue() {
	eyebeam2018_enqueue_css('fonts/eyebeam-bold.css');
	eyebeam2018_enqueue_css('fonts/arial-monospaced.css');
	eyebeam2018_enqueue_css('style.css');
	eyebeam2018_enqueue_js('js/eyebeam2018.js', array('jquery'));
}
add_action('wp_enqueue_scripts', 'eyebeam2018_enqueue');

// Helper for theme images
function eyebeam2018_img_src($path) {
	$version = filemtime(__DIR__ . "/$path");
	echo get_stylesheet_directory_uri() . "/$path?ver=$version";
}

// Register a hero item
function eyebeam2018_hero($hero) {
	$GLOBALS['eyebeam2018']['heroes'][] = $hero;
}

// Register a module item
function eyebeam2018_module($module) {
	$GLOBALS['eyebeam2018']['modules'][] = $module;
}

// Render each hero item's template
function eyebeam2018_render_heroes() {
	foreach ($GLOBALS['eyebeam2018']['heroes'] as $hero) {

		// See this curr_hero global? It's important! It's how the
		// page template knows what stuff to show.
		$GLOBALS['eyebeam2018']['curr_hero'] = $hero;

		get_template_part('templates/page-hero', $hero['type']);
	}
}

// Render each module item's template
function eyebeam2018_render_modules() {

	$class = 'module-container';

	// Okay this is weird, but it's necessary for getting the module order
	// right on mobile. Basically, we swap the TOC with the first module.
	if (count($GLOBALS['eyebeam2018']['modules']) > 1 &&
	    $GLOBALS['eyebeam2018']['modules'][0]['type'] == 'toc') {

		$toc = $GLOBALS['eyebeam2018']['modules'][0];
		$module = $GLOBALS['eyebeam2018']['modules'][1];

		$GLOBALS['eyebeam2018']['modules'][0] = $module;
		$GLOBALS['eyebeam2018']['modules'][1] = $toc;

		$class .= ' module-swap-toc';
	}

	echo "<div class=\"$class\">\n";

	foreach ($GLOBALS['eyebeam2018']['modules'] as $module) {

		// See this curr_module global? It's important! It's how the
		// page template knows what stuff to show.
		$GLOBALS['eyebeam2018']['curr_module'] = $module;

		get_template_part('templates/page-module', $module['type']);
	}

	echo "<br class=\"clear\">\n";
	echo "</div>\n";
}

// Returns an array of resident posts for a given year
function eyebeam2018_get_residents($year = null) {

	if (empty($year)) {
		$year = date('Y');
	}
	$year = intval($year);

	$args = array(
		'post_type' => 'resident',
		'posts_per_page' => -1,
		'orderby'=> 'meta_value_num',
		'meta_key' => 'end_year',
		'meta_query' => array(
			'relation' => 'AND',
			'start_clause' => array(
				'key'=> 'start_year',
				'value'=> $year,
				'compare'=> '<='
			),
			'end_clause' => array(
				'key'=> 'end_year',
				'value' => $year,
				'compare'=> '>='
			),
		)
	);

	$posts = get_posts($args);
	return $posts;
}

// AJAX handler for resident requests (by year)
function eyebeam2018_ajax_residents() {
	if (empty($_GET['year'])) {
		die('No year specified');
	}
	$year = $_GET['year'];
	$residents = eyebeam2018_get_residents($year);

	foreach ($residents as $resident) {
		$GLOBALS['eyebeam2018']['curr_collection_item'] = $resident;
		get_template_part('templates/collection-resident-item');
	}
	exit;
}
add_action('wp_ajax_eyebeam2018_residents', 'eyebeam2018_ajax_residents');
add_action('wp_ajax_nopriv_eyebeam2018_residents', 'eyebeam2018_ajax_residents');

// Outputs a video embed from its permalink URL
function eyebeam2018_video_embed($video_url) {

	// TODO: make this work with more video hosts, currently we only support
	// YouTube. I mean, should we let oembed or shortcodes handle this? It's
	// not like this is the first video to get embedded onto WordPress. for
	// now we just do it the dumb/easy way. (20180303/dphiffer)

	$regexes = array(
		'/youtube\.com\/watch\?.*v=([^&]+)/' => 'eyebeam2018_youtube_embed',
		'/youtu\.be\/([^?]+)/' => 'eyebeam2018_youtube_embed'
	);

	foreach ($regexes as $regex => $handler) {
		if (preg_match($regex, $video_url, $matches)) {
			$handler($matches);
			return;
		}
	}

	echo "<!-- could not render video embed for $video_url -->\n";
}

// Handler for YouTube video embeds
function eyebeam2018_youtube_embed($matches) {
	$id = $matches[1];
	$src = "https://www.youtube.com/embed/$id";
	$dimensions = 'width="640" height="360"';
	$args = 'frameborder="0" allow="autoplay; encrypted-media" allowfullscreen';
	$embed = "<iframe $dimensions src=\"$src\" $args></iframe>";
	$embed = "<div class=\"video-container\">$embed</div>\n";
	echo $embed;
}

// AJAX handler for email subscribers
function eyebeam2018_subscribe() {

	$rsp = eyebeam2018_subscribe_request();
	$headers = apache_request_headers();
	if (! empty($headers['X-Requested-With']) &&
	    $headers['X-Requested-With'] == 'XMLHttpRequest') {
		header('Content-Type: application/json');
		echo json_encode($rsp);
		exit;
	} else if (! empty($headers['Referer'])) {
		$redirect = $headers['Referer'];
		$result = "subscribed={$rsp['ok']}";
		if (preg_match('/subscribed=[^&]+/', $redirect)) {
			$redirect = preg_replace('/subscribed=[^&]+/', $result, $redirect);
		} else if (strpos($redirect, '?') === false) {
			$redirect .= "?$result";
		} else {
			$redirect .= "&$result";
		}
		$redirect .= '#subscribe';
		header("Location: $redirect");
		exit;
	} else if ($rsp['ok'] == 1) {
		echo "Thanks for subscribing!";
	} else if ($rsp['error']) {
		echo $rsp['error'];
	} else {
		echo "That didn’t work for some reason.";
	}
}
add_action('wp_ajax_eyebeam2018_subscribe', 'eyebeam2018_subscribe');
add_action('wp_ajax_nopriv_eyebeam2018_subscribe', 'eyebeam2018_subscribe');

// Actually *do* the API request to Mailchimp
function eyebeam2018_subscribe_request() {

	// I mean, yes, I know there are plugins that do this sort of thing. But
	// ultimately it's an API, and we should be able to debug it when it
	// breaks. So we just use cURL and typing. (20180303/dphiffer)

	if (! defined('MAILCHIMP_API_KEY')) {
		return array(
			'ok' => 0,
			'error' => 'MAILCHIMP_API_KEY is undefined.'
		);
	} else if (! defined('MAILCHIMP_LIST_ID')) {
		return array(
			'ok' => 0,
			'error' => 'MAILCHIMP_LIST_ID is undefined.'
		);
	} else if (! empty($_POST['first_name']) &&
	           ! empty($_POST['last_name']) &&
	           ! empty($_POST['email']) &&
	           preg_match('/\w+@\w+\.\w+/', $_POST['email'])) {
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$email = $_POST['email'];

		if (! preg_match('/^[a-z0-9]+-(\w+)$/', MAILCHIMP_API_KEY, $matches)) {
			return array(
				'ok' => 0,
				'error' => 'Invalid MAILCHIMP_API_KEY.'
			);
		}
		$dc = $matches[1];

		$base_url = "https://$dc.api.mailchimp.com/3.0";
		$list_id = MAILCHIMP_LIST_ID;
		$subscriber = trim($email);
		$subscriber = strtolower($subscriber);
		$subscriber = md5($subscriber);
		$url = "$base_url/lists/$list_id/members/$subscriber";
		$data = json_encode(array(
			'email_address' => $email,
			'status' => 'subscribed',
			'merge_fields' => array(
				'FNAME' => $first_name,
				'LNAME' => $last_name
			)
		));
		$headers = array(
			'Content-Type: application/json'
		);
		$userpwd = ':' . MAILCHIMP_API_KEY;

		//dbug($url);
		//dbug($data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);

		$json = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		//dbug($status);
		//dbug($json);

		if ($status != 200) {
			return array(
				'ok' => 0,
				'error' => 'Bad response from MailChimp API.'
			);
		}

		$rsp = json_decode($json, 'as hash');
		if (empty($rsp['status'])) {
			return array(
				'ok' => 0,
				'error' => 'Uncertain response from MailChimp API.'
			);
		}

		return array(
			'ok' => 1,
			'status' => $rsp['status']
		);
	} else {
		return array(
			'ok' => 0,
			'error' => 'Sorry, all the fields are required.'
		);
	}
}

// AJAX handler for donations
function eyebeam2018_donate() {

	$rsp = eyebeam2018_donate_request();
	$headers = apache_request_headers();
	if (! empty($headers['X-Requested-With']) &&
	    $headers['X-Requested-With'] == 'XMLHttpRequest') {
		header('Content-Type: application/json');
		echo json_encode($rsp);
		exit;
	} else if (! empty($headers['Referer'])) {
		$redirect = $headers['Referer'];
		$result = "donation={$rsp['ok']}";
		if (preg_match('/donation=[^&]+/', $redirect)) {
			$redirect = preg_replace('/donation=[^&]+/', $result, $redirect);
		} else if (strpos($redirect, '?') === false) {
			$redirect .= "?$result";
		} else {
			$redirect .= "&$result";
		}
		$redirect .= '#donate';
		header("Location: $redirect");
		exit;
	} else if ($rsp['ok'] == 1) {
		echo "Thank you so much for your donation!";
	} else if ($rsp['error']) {
		echo $rsp['error'];
	} else {
		echo "Sorry, that didn’t work for some reason.";
	}
	exit;
}
add_action('wp_ajax_eyebeam2018_donate', 'eyebeam2018_donate');
add_action('wp_ajax_nopriv_eyebeam2018_donate', 'eyebeam2018_donate');

// Actually *do* the Stripe API request
function eyebeam2018_donate_request() {

	dbug('eyebeam2018_donate_request');

	$dir = __DIR__;
	require_once("$dir/lib/stripe-php/init.php");

	$values = eyebeam2018_donate_normalize($_POST);
	dbug('eyebeam2018_donate_normalize:', $values);

	if (! defined('STRIPE_TEST_KEY') ||
	    ! defined('STRIPE_TEST_SECRET') ||
	    ! defined('STRIPE_LIVE_KEY') ||
	    ! defined('STRIPE_LIVE_SECRET')) {
		return array(
			'ok' => 0,
			'error' => 'Stripe API keys are not setup.'
		);
	} else if (eyebeam2018_donate_validate($values)) {

		if (defined('STRIPE_USE_LIVE') && STRIPE_USE_LIVE) {
			$key = STRIPE_LIVE_KEY; // This isn't actually used here
			$secret = STRIPE_LIVE_SECRET;
		} else {
			$key = STRIPE_TEST_KEY; // This isn't actually used here
			$secret = STRIPE_TEST_SECRET;
		}

		dbug('setting API key...');

		\Stripe\Stripe::setApiKey($secret);

		dbug('creating charge...');

		try {
			$charge = \Stripe\Charge::create(array(
				'amount' => $values['amount'],
				'currency' => 'usd',
				'description' => 'Donation to Eyebeam. Thank you!',
				'source' => $values['token']
			));
		} catch (Exception $e) {
			dbug($e);
			return array(
				'ok' => 0,
				'error' => 'Error from Stripe API: ' . $e->getMessage()
			);
		}

		dbug($charge);

		return array(
			'ok' => 1
		);

	} else {
		return array(
			'ok' => 0,
			'error' => 'Sorry, all the fields are required.'
		);
	}
}

// Massage the donate submission values
function eyebeam2018_donate_normalize($raw) {
	$vars = array(
		'first_name',
		'last_name',
		'email',
		'amount',
		'token'
	);
	$normalized = array();
	foreach ($vars as $var) {
		$normalized[$var] = trim($raw[$var]);
		if ($var == 'email') {
			$normalized[$var] = strtolower($normalized[$var]);
		} else if ($var == 'amount' &&
		           $normalized['amount'] == 'other') {
			$normalized[$var] = trim($raw['amount_other']);
		}
	}
	$normalized['amount'] = str_replace('$', '', $normalized['amount']);
	return $normalized;
}

// Ensure the donate submission is valid
function eyebeam2018_donate_validate($values) {
	$required = array(
		'first_name',
		'last_name',
		'email',
		'amount',
		'token'
	);
	$numeric = array(
		'amount'
	);
	foreach ($required as $var) {
		if (empty($values[$var])) {
			return false;
		}
		if (in_array($var, $numeric) &&
		    ! is_numeric($values[$var])) {
			return false;
		}
	}
	if (! preg_match('/\w+@\w+\.\w+/', $values['email'])) {
		return false;
	}
	return true;
}

// A filter for the_content, that sets a 'post_intro' global var
function eyebeam2018_extract_intro($content) {
	$sections = preg_split('/<hr\s*\/?>/', $content);
	if (count($sections) < 2) {
		return $content;
	}
	if (preg_match('/<i>(.+?)<\/i>/ims', $sections[0], $matches)) {
		$intro = array_shift($sections);

		// TODO: get some htmlpurifier in the mix here
		$intro = preg_replace('/<span[^>]*>/', '', $intro);
		$intro = preg_replace('/<\/span[^>]*>/', '', $intro);
		$intro = preg_replace('/<i[^>]*>/', '', $intro);
		$intro = preg_replace('/<\/i[^>]*>/', '', $intro);

		$GLOBALS['eyebeam2018']['post_intro'] = $intro;
		return implode('<br>', $sections);
	}
	return $content;
}

// Inserts the_content-like content from ACF
function eyebeam2018_content_fields($content) {
	if (get_field('event_info')) {
		return get_field('event_info');
	}
	return $content;
}

// Tweak shortcode outputs
function eyebeam2018_shortcode_filter($output, $tag, $attrs) {
	if ($tag == 'embed') {
		return "<div class=\"video-container\">$output</div>";
	}
	return $output;
}
add_filter('do_shortcode_tag', 'eyebeam2018_shortcode_filter', 10, 3);

// Inserts a secret blog post as an HTML comment
function eyebeam2018_view_source() {
	if (empty($GLOBALS['eyebeam2018']['view_source_post'])) {
		return;
	}

	$slug = $GLOBALS['eyebeam2018']['view_source_post'];
	$dir = __DIR__;
	$header = "$dir/lib/.ignore/00-header.txt";
	$path = "$dir/lib/.ignore/$slug.txt";

	if (! file_exists($path)) {
		return;
	}

	echo "<!--\n";
	echo file_get_contents($header);
	echo "\n///// VIEW SOURCE BLOG: $slug /////\n\n";
	echo file_get_contents($path);
	echo "-->\n";
}
add_action('eyebeam2018_view_source', 'eyebeam2018_view_source');

// Register a secret blog post for a given page
function eyebeam2018_view_source_post($slug) {
	$GLOBALS['eyebeam2018']['view_source_post'] = $slug;
}

// This requires that DBUG_PATH is set in wp-config.php.
function dbug() {
	if (empty($GLOBALS['dbug_fh'])) {
		if (! defined('DBUG_PATH')) {
			return;
		}
		$fh = fopen(DBUG_PATH, "a");
		$GLOBALS['dbug_fh'] = $fh;
		$GLOBALS['dbug_start'] = microtime(true);
		fwrite($fh, "----------------------\n");
	}
	$fh = $GLOBALS['dbug_fh'];
	$sec = microtime(true) - $GLOBALS['dbug_start'];
	$sec = number_format($sec, 2);
	$sec = ($sec < 10) ? "0$sec" : $sec;
	$args = func_get_args();
	foreach ($args as $arg) {
		if (! is_scalar($arg)) {
			$arg = print_r($arg, true);
			$arg = trim($arg);
		}
		fwrite($fh, "[$sec] $arg\n");
	}
}
