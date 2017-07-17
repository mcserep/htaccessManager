<!DOCTYPE html>
<html>
<head>
  <title>Example repository access manager</title>
  <meta charset="utf-8">
  <meta name="description" content="Access manager for example repository">
  <meta name="author" content="Cserép Máté">
  <meta name="robots" content="noindex,nofollow">
</head>

<body>
<?php

require_once 'htaccessManager.php';

try
{
  $manager = new htaccessManager('example', array('admin1', 'admin2'));
  $manager->show($_SERVER['PHP_AUTH_USER']);
}
catch(Exception $ex)
{
  echo '<p><b>Error: ' . $ex->getMessage() . '</b></p>';
}

?>
</body>
</html>