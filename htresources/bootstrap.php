<?PHP
require $_SERVER['DOCUMENT_ROOT']."/../htresources/config.php";

@define('BOOTSTRAP_VERSION', "0.0.0");
@define('HTML_VERSION', "0.0.0");
@define('CSS_VERSION', "0.0.0");

function checkVersion($checkVersion, $version) {
	if(!$checkVersion)
		return false;
	return $checkVersion == $version;
}

function loadBootstrap($version) {
	if(checkVersion($version, BOOTSTRAP_VERSION))
		return;
	ob_start();
	echo "localStorage.bootstrapVersion = ".json_encode(BOOTSTRAP_VERSION).";\n";
	require $_SERVER['DOCUMENT_ROOT']."/../htresources/bootstrap.js";
	return str_replace("\n\s+", "", ob_get_clean());
}

function loadHTML($version) {
	if(checkVersion($version, HTML_VERSION))
		return;
	return "<!--version=".json_encode(HTML_VERSION)."-->\n".str_replace("\n\s+", "", file_get_contents($_SERVER['DOCUMENT_ROOT']."/../htresources/html.html"));
}

function loadCSS($version) {
	if(checkVersion($version, CSS_VERSION))
		return;
	return "/* version=".json_encode(CSS_VERSION)." */\n\n".str_replace("\n\s+", "", file_get_contents($_SERVER['DOCUMENT_ROOT']."/../htresources/css.css"));
}
?>
