<?php
  $db_uri   = getenv("MYSQL_URI");
  $db_port  = getenv("MYSQL_PORT");
  $db_host  = getenv("MYSQL_HOSTNAME");
  $db_name  = getenv("MYSQL_SCHEMA");
  $dbu_name = getenv("MYSQL_USERNAME");
  $dbu_pass = getenv("MYSQL_PASSWORD");

  // Convert $db_uri to $db_host (ip)...

  $dbh = new \PDO('mysql:host=' . $db_host . ';port=' . $db_port . ';dbname=' . $db_name, $dbu_name, $dbu_pass);
  if(!$dbh) { die("Sorry. Your request could not be processed at this time. (MSG: SR100)"); }

  function simple_query($dbh, $sql, $params = array(), $remove_extra_spaces = true) {
    $out = array();
    if( $remove_extra_spaces ) {
      $sql = trim(preg_replace('/\s\s+/', ' ', $sql));
    }
    $sth = $dbh->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $sth->execute($params);
    for($i=0; $row = $sth->fetch(); $i++) { $out[$i] = $row; }
    return $out;
  }
?>
