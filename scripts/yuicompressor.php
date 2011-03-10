<?php

if($_POST['input'] !== '') {
  $filename = 'tmp/' . md5($_POST['input']) . '.js';
  
  $fp = fopen($filename, 'w');
  fwrite($fp, $_POST['input']);
  fclose($fp);
  
  echo exec('java -jar yuicompressor-2.4.2.jar ' . $filename);
  
  // cleanup
  unlink($filename);
}

?>