<?PHP
require $_SERVER['DOCUMENT_ROOT']."/resource/config.php";

// localStorage.bootstrap= "null";
// localStorage.html = "null";
// localStorage.css = "null";
// localStorage.bootstrapVersion = "null";
// localStorage.htmlVersion      = "null";
// localStorage.cssVersion       = "null";
?>
<script id="bootstrap">
var script = document.createElement('script');
script.innerHTML = localStorage.bootstrap;
document.head.appendChild(script);

async function updateBootstrap() {
  const firstTime = localStorage.bootstrap=="";
  if(firstTime) {
    localStorage.bootstrapVersion = "null";
    localStorage.htmlVersion      = "null";
    localStorage.cssVersion       = "null";
  }

  const bootstrap = await fetch('/resource/bootstrap.php?type=bootstrap&version='+localStorage.bootstrapVersion, {method: 'post', mode: 'no-cors'}).then(r=>r.text());

  if(firstTime || bootstrap!="") {
    localStorage.bootstrap = bootstrap;
    if(firstTime) {
      eval(localStorage.bootstrap);
      console.warn("Welcome! We've detected it's your first time, and have just set everything up. To work the site needs a reload.")
      window.location.reload();
    }
    console.log("Script Update finished!, will be implemented on reload");
  }
}
updateBootstrap();
</script>
