<?PHP
require $_SERVER['DOCUMENT_ROOT']."/../htresources/config.php";
$version = "0.0.0";
if(empty($_SERVER['SHELL'])) {
  switch (mb_strtolower($_REQUEST['type'])) {
    case "bootstrap":
      $version = "0.0.0";
      $version = '"'.time().'"';
      checkVersion();
      loadBootstrap();
      break;
    case "html":
      $version = "0.0.0";
      $version = '"'.time().'"';
      checkVersion();
      loadHTML();
      break;
    case "css":
      $version = "0.0.0";
      $version = '"'.time().'"';
      checkVersion();
      loadCSS();
      break;
      default: die('valid types are ["bootstrap", "html", "css"]');
  }
}
function checkVersion() {
  global $version;
  if(str_replace(".","",$_REQUEST['version']) == str_replace(".","",$version)) {
    die("");
  }
}

function loadBootstrap() {


  // // REST API arguments
  // $apiArgs = array(
  //   'compilation_level'=>'ADVANCED_OPTIMIZATIONS',
  //   'output_format' => 'text',
  //   'output_info' => 'compiled_code'
  // );
  //
  // ob_start();
  // require $_SERVER['DOCUMENT_ROOT']."/../htresources/bootstrap.js";
  // $js = ob_get_clean();
  // // echo $js;
  //
  // $js = 'alert("hello")';
  //
  // $args = 'js_code=' . urlencode($js);
  // foreach ($apiArgs as $key => $value) {
  //   $args .= '&' . $key .'='. urlencode($value);
  // }
  //
  // // API call using cURL
  // $call = curl_init();
  // curl_setopt_array($call, array(
  //   CURLOPT_URL => 'http://closure-compiler.appspot.com/compile',
  //   CURLOPT_POST => 1,
  //   CURLOPT_POSTFIELDS => $args,
  //   CURLOPT_RETURNTRANSFER => 1,
  //   CURLOPT_HEADER => 0,
  //   CURLOPT_FOLLOWLOCATION => 0
  // ));
  // $jscomp = curl_exec($call);
  // print_r($jscomp);
  // print_r(curl_exec($call));
  // curl_close($call);
  // print_r($jscomp);
  //
  // return $jscomp;

  ob_start();
  global $version;
  echo "localStorage.bootstrapVersion = ".json_encode($version).";\n";
  require $_SERVER['DOCUMENT_ROOT']."/../htresources/bootstrap.js";
  return str_replace("\n\s+", "", ob_get_clean());
}

function loadHTML() {
  // global $version;
  // echo "<!--version=".json_encode($version)."-->\n";
  return str_replace("\n\s+", "", file_get_contents($_SERVER['DOCUMENT_ROOT']."/../htresources/html.html"));
}

function loadCSS() {
  // global $version;
  // echo "/* version=".json_encode($version)." */\n\n";
  return str_replace("\n\s+", "", file_get_contents($_SERVER['DOCUMENT_ROOT']."/../htresources/css.css"));
}
?>
