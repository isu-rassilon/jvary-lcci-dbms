<script language="JavaScript" src="jquery/DataTables-1.7.6/media/js/jquery.dataTables.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript" charset="utf-8">
  $(document).ready(function() {
    $('#resultsTable').dataTable({
      "bPaginate": true,
      "bLengthChange": true,
      "bSort": true,
      "bFilter": false,
//      "bJQueryUI": true,
      "sPaginationType": "full_numbers",
      "iDisplayLength": 100});
  } );
</script>
<!--p><?php echo "Search Results: " . mysql_num_rows($sd_query_result_handle) . " records found.\n"; ?></p-->
<table border="1" id="resultsTable" class="display">
<?php
  $fields = array("runID", "name", "Z", "N", "nshell_min_Z", "nshell_max_Z", 
                  "nshell_min_N", "nshell_max_N", "Nmax", "Nstates", 
                  "twiceJ", "twiceMj", "START_DATE");

  echo "<thead><tr>";
  foreach( $fields as $field) {
    if( $field == "twiceJ" ) {
      echo "<th>J</th>";
    } elseif( $field == "twiceMj" ) {
      echo "<th>m<sub>J</sub></th>";
    } else {
      echo "<th>".toFieldName($field)."</th>";
    }
  }
  echo "</tr></thead>\n<tbody>\n";

  while($row = mysql_fetch_assoc($sd_query_result_handle)){
    $runID = $row['runID'];
    echo "<tr onclick=\"JavaScript: dehighlightTR(this); goID(".$runID.");\">";
    foreach( $fields as $field ) {
      if( $field == "twiceJ" || $field == "twiceMj" ) {
        if( $field == "twiceJ" && $row[$field] == "-1" ) {
          echo "<td> </td>";
        } else {
          echo "<td><a href=\"details.php?ID=".$runID."\">". twiceFieldToFraction($row[$field]) ."</a></td>";
        }
      } else {
        echo "<td><a href=\"details.php?ID=".$runID."\">". $row[$field] ."</a></td>";
      }
    }
    echo "</tr>\n";
  }
  echo "</tbody>\n";
  echo "</table>\n";
  //echo "Search Results: " . mysql_num_rows($sd_query_result_handle) . " records found.\n";

  // If there was only one result, redirect.
  if( mysql_num_rows($sd_query_result_handle) == 1 ) {
    echo "<br /><br />";
    echo "<div>Automatically redirecting to only match...</div>";
    echo "<script language=\"JavaScript\">";
    echo "  setTimeout(\"self.location.href='details.php?ID=".$runID."'\", 2500);";
    echo "</script>";
  }
?>
