<?php
  // Start the page happily.
  $htmlTitle = "DBMS Search Results";
  include "../include/htmlhead.php";
  include "../include/functions.php";

  // Ritu liked timing statements, so let's keep that.
  $mtime     = explode(' ', microtime()); 
  $starttime = $mtime[1] + $mtime[0];

  // Connect to the database.
  include "../include/dbauth.php";

  // Start Creating Query.
  $sql  = "select r.runID, u.name, r.Z, r.N, r.nshell_min_Z, r.nshell_max_Z, ";
  $sql .= "       r.nshell_min_N, r.nshell_max_N, r.Nmax, r.Nstates, r.twiceJ, ";
  $sql .= "       r.twiceMj, r.START_DATE ";
  $sql .= "from lcci.RUN r ";
  $sql .= "join lcci.USERS u ";
  $sql .= "  on r.username = u.username ";

  // Create a function to simplify the form -> sql manipulation.
  function addToWhere($cond, $addStr) {
    if( $cond == "where" ) {
      return " " . $addStr;
    } else {
      return " and " . $addStr;
    }
  }

  function sqlParam($rname, $cname, $cond, $ignoreIfValue, $chkNumeric, $chkInt, $chkBool) {
    if( isset($_REQUEST[$rname]) ) {
      $rValue = trim($_REQUEST[$rname]);
      // Don't waste time processing if it's just the default/ignore value.
      if( $rValue != $ignoreIfValue ) {
        if( $chkNumeric ) {
          if( is_numeric($rValue) ) {
            if( $chkInt ) {
                return addToWhere($cond, $cname . "=" . $rValue);
            } else {
              return addToWhere($cond, $cname . "=" . $rValue);
            }
          }
        } elseif( $chkBool ) {

        } else {
          return addToWhere($cond, $cname . "='" . $rValue . "'");
        }
      }
    }
  }

  // Create Query Conditions from $_GET and $_POST.
  $cond  = "where";
  $cond .= sqlParam("Z", "Z", $cond, "", true, true, false);
  $cond .= sqlParam("N", "N", $cond, "", true, true, false);
  $cond .= sqlParam("V2B", "2B_potential", $cond, "any", false, false, false);
  $cond .= sqlParam("V3B", "3B_potential", $cond, "any", false, false, false);
  $cond .= sqlParam("V4B", "4B_potential", $cond, "any", false, false, false);
  $cond .= sqlParam("ExtF", "ext_field", $cond, "any", false, false, false);
  $cond .= sqlParam("nmax", "Nmax", $cond, "", true, true, false);
  $cond .= sqlParam("nstates", "Nstates", $cond, "", true, true, false);
  $cond .= sqlParam("totalJ", "twiceJ", $cond, "", true, true, false);
  $cond .= sqlParam("mj", "twiceMj", $cond, "", true, true, false);
  $cond .= sqlParam("refstate", "refstate", $cond, "", true, true, false);
  $cond .= sqlParam("ns_max_z", "nshell_max_Z", $cond, "", true, true, false);
  $cond .= sqlParam("ns_min_z", "nshell_min_Z", $cond, "", true, true, false);
  $cond .= sqlParam("ns_max_n", "nshell_max_N", $cond, "", true, true, false);
  $cond .= sqlParam("ns_min_n", "nshell_min_Z", $cond, "", true, true, false);
  $cond .= sqlParam("machineID", "machineID", $cond, "any", false, false, false);
  $cond .= sqlParam("info_file", "info_file", $cond, "", false, false, false);
  $cond .= sqlParam("runID", "runID", $cond, "", true, true, false);
  $cond .= sqlParam("userID", "u.id", $cond, "any", true, true, false);

  // Run the SQL Query.
  if( $cond != "where" ) {
    $sql .= $cond;
  }
  $res = simple_query($dbh, $sql); 

  // Display the results.
  $sd_query_result_handle = $res;
  $sd_URL_prefix = "search_results.php?".$uget;
  include "../include/search_display.php";

  // Finishing the timing statement. How did we do? 36 hours? What?!? ;-)
  $mtime     = explode(' ', microtime());
  $endtime   = $mtime[1] + $mtime[0];
  $totaltime = ($endtime - $starttime); 
  echo '<br />*This page was created in ' .$totaltime. ' seconds.'; 

  // End the page 
  include "../include/htmltail.php";
?>
