<?
function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

// Translate a database field name to a more friendly form.
// Default if unknown: passed field name.
function toFieldName($s) {
  switch($s) {
    case "nshell_min_Z": return "nshell<br />min Z"; break;
    case "nshell_max_Z": return "nshell<br />max Z"; break;
    case "nshell_min_N": return "nshell<br />min N"; break;
    case "nshell_max_N": return "nshell<br />max N"; break;
    case "START_DATE":   return "Start Date";        break;
    case "END_DATE":     return "End Date";          break;
    case "twiceJ":       return "2*j";               break;
    case "twiceMj":      return "2*m<sub>j</sub>";   break;
    case "info_file":    return "DBMS Info File";    break;
    default: return $s;
  }
}

// Turn something like twiceJ or twiceMj into J or Mj.
function twiceFieldToFraction($s) {
  // If it isn't a number, return it.
  $s = trim($s);
  if( !is_numeric($s) ) { return $s; }
  else {
    // Is it an integer?
    if( $s != (int)$s ) {
      // No.
      return $s;
    } else {
      // Is it even?
      $t = trim($s)/2.0;
      if( $t == (int)$t ) {
        // It is!
        return $t;
      } else {
        // Nope!
        return $s."/2";
      }
    }
  }
}

function parityText($parityInt) {
  if( !is_numeric($parityInt) ) {
    return $parityInt;
  } else {
    switch($parityInt) {
      case "-1": return "negative (-)"; break;
      case "+1": return "positive (+)"; break;
      case "0":  return "both"; break;
      default: return $parityInt; break;
    }
  }
}

?>
