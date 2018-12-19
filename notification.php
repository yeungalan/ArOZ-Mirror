<?php
include '../auth.php';
?>
<?php
header('Content-Type: application/json');
echo "[";
foreach ($_COOKIE as $key=>$val)
  {
	  if(strpos($key, 'MSG_') !== false){
			$var = $var. '["'.$val.'"],';
	  }
  }
  echo substr($var, 0, -1);
echo "]";
if(isset($_GET["msg"])){
	setcookie("MSG_".time(), $_GET["msg"],time()+3600);
}
?>