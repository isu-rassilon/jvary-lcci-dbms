<!-- Begin "Chart of Results" -->
<?php include "../include/dbauth.php"; $dbc = mysql_connect($db_host, $dbu_name, $dbu_pass); ?>
<style type="text/css">
.corHead {
  font-size: 14pt;
  font-weight: bold;
}

.cor {
  border: 1px solid black;
  border-collapse: collapse;
}

.cor tr {

}

.corH {
  background-color: yellow;
}

.corD {
  background-color: white;
}

.cor td {
  border: 1px solid grey;
  text-align: center;
  height: 42px;
  width: 42px;
}

.cor th {
  background-color: #CBCBCB;
  border: 1px solid grey;
  text-align: center;
  height: 42px;
  width: 42px;
}
</style>
<script language="JavaScript" type="text/javascript">
function corH(ele) {
  var tbl  = ele.parentNode.parentNode;
  var rloc = ele.rowIndex;
  var cloc = ele.cellIndex;
}

function corD(ele) {
  var tbl  = ele.parentNode.parentNode;
  var rloc = ele.rowIndex;
  var cloc = ele.cellIndex;
}

function corSet(fieldName, fieldValue) {
  document.getElementById(fieldName).value = fieldValue;
  document.corForm.submit();
}

function corSetChange(fieldName, ele) {
  corSet(fieldName, ele.value);
}
</script>
<div align="center">
<a name="ResultSummary"></a>
<span class="corHead">Summary of Available Calculations</span>
<table cellpadding="2px">
<?php
  // Was a form submitted? If so, use those values. Otherwise, defaults.
  $V2B = !isset($_POST['V2B']) ? 'Any' : mysql_escape_string(trim($_POST['V2B']));
  $V3B = !isset($_POST['V3B']) ? 'Any' : mysql_escape_string(trim($_POST['V3B']));
  $V4B = !isset($_POST['V4B']) ? 'Any' : mysql_escape_string(trim($_POST['V4B']));
  $X_F = !isset($_POST['X_F']) ? 'Any' : mysql_escape_string(trim($_POST['X_F']));
  $UID = !isset($_POST['UID']) ? 'Any' : mysql_escape_string(trim($_POST['UID']));

  function corLink($fField, $fValue) {
    return "<a href=\"JavaScript: corSet('".$fField."','".$fValue."')\">".trim($fValue)."</a>";
  }

  function genPotentialRow($fField, $pField, $pSelected, $pTitle, $dbc) {
    $qry = "select ".$pField." from lcci.RUN group by ".$pField;
    $res = mysql_query($qry, $dbc);
    if( $res ) {
      if( mysql_num_rows($res) == 0 ) {
        // Do nothing.
      } elseif( mysql_num_rows($res) == 1) {
        $row = mysql_fetch_assoc($res);
        // Only show if not "NONE".
        if( trim($row[$pField]) != "NONE" ) {
          print "<tr><td><strong>".$pTitle.":</strong></td><td><em>".$pSelected."</em></td><td>";
          if( $pSelected != 'Any' ) { print "&nbsp;&nbsp;".corLink($fField,"Any"); }
          if( $row[$pField] != $pSelected ) {
            print "&nbsp;&nbsp;" . corLink($fField,$row[$pField]);
          }
        }
      } else {
        print "<tr><td><strong>".$pTitle.":</strong></td><td><em>".$pSelected."</em></td><td>";
        if( $pSelected != 'Any' ) { print "&nbsp;&nbsp;".corLink($fField,"Any"); }
        while( $row = mysql_fetch_assoc($res) ) {
          // We don't need to print the same line twice.
          if( $row[$pField] != $pSelected ) {
            print "&nbsp;&nbsp;" . corLink($fField,$row[$pField]);
          }
        }
      }
    }
    mysql_free_result($res);
    print "</td></tr>\n";
  }

  genPotentialRow("V2B", "2B_potential", $V2B, "2 Body Potential", $dbc);
  genPotentialRow("V3B", "3B_potential", $V3B, "3 Body Potential", $dbc);
  genPotentialRow("V4B", "4B_potential", $V4B, "4 Body Potential", $dbc);

  print "<tr><td><strong>External Field:</strong></td>";
  $qry = "select ext_field from lcci.RUN group by ext_field";
  $res = mysql_query($qry, $dbc);
  if( $res ) {
    print "<td colspan=2><select onChange=\"JavaScript: corSetChange('X_F',this);\" id=\"extFieldSelect\" style=\"width:380px\">";
    print "<option value=\"".$X_F."\" selected><em>".$X_F."</em></option>";
    print "<option value=\"Any\"><em>Any</em></option>";
    while( $row = mysql_fetch_assoc($res) ) {
      print "<option value=\"". $row['ext_field']. "\">" . $row['ext_field'] . "</option>";
    }
  }
  mysql_free_result($res);
  print "</select></td></tr>\n";

  print "<tr><td><strong>User:</strong></td>";
  $qry = "select u.name, u.id from lcci.RUN r join lcci.USERS u on r.username = u.username group by name, id order by name, id";
  $res = mysql_query($qry, $dbc);
  if( $res ) {
    print "<td colspan=2><select onChange=\"JavaScript: corSetChange('UID',this);\" id=\"userSelect\" style=\"width:380px\">";
    print "<option value=\"Any\"><em>Any</em></option>";
    while( $row = mysql_fetch_assoc($res) ) {
      if( $row['id'] == $UID ) { $optString = "selected"; } else { $optString = ""; }
      print "<option value=\"". $row['id']. "\"". $optString .">" . $row['name'] . "</option>";
    }
  }
  mysql_free_result($res);
  print "</td></tr>\n";
?>
</table>
<div>
<img src="image/proton-arrow.png" style="float: left; vertical-align: middle;" />
<table class="cor">
<?php
  // Let's get some statistics on the number of runs and calculations.
  $qry = "select (select count(*) from lcci.RUN) as numRuns, (select count(*) from lcci.RES_FILE) as numCalcs";
  $res = mysql_query($qry, $dbc);
  if( !$res ) { die("DB Issues. Sorry. (Error=CoR080)"); }
  $row = mysql_fetch_assoc($res);
  $total_calculations_stored = $row['numCalcs'];
  $total_runs_stored = $row['numRuns'];
  mysql_free_result($res);

  // More statistics...number of different nuclei.
  $qry = "select Z, N, count(*) as T from lcci.RUN group by Z, N order by Z desc, N asc";
  $res = mysql_query($qry, $dbc);
  if( !$res ) { die("DB Issues. Sorry. (Error=CoR090)"); }
  $total_distinct_nuclei = mysql_num_rows($res);
  mysql_free_result($res);

  // Check how big the table needs to be.
  $qry = "select max(Z) as maxZ, max(N) as maxN from lcci.RUN";
  $res = mysql_query($qry, $dbc);
  if( !$res ) { die("DB Issues. Sorry. (Error=CoR100)"); }
  $row = mysql_fetch_assoc($res);
  $zmx = $row['maxZ'];
  $nmx = $row['maxN'];
  mysql_free_result( $res );

  // Actual result counts.
  $qwc = " where 1=1 "; // Query "Where" Clause...
  if( $V2B != "Any" ) { $qwc .= " and 2B_potential = '".$V2B."'"; }
  if( $V3B != "Any" ) { $qwc .= " and 3B_potential = '".$V3B."'"; }
  if( $V4B != "Any" ) { $qwc .= " and 4B_potential = '".$V4B."'"; }
  if( $X_F != "Any" ) { $qwc .= " and ext_field = '".$X_F."'"; }
  if( $UID != "Any" ) { $qwc .= " and username in (select username from lcci.USERS where id = ".$UID.") "; }
  $qry = "select Z, N, count(*) as T, max(runID) as RunID from lcci.RUN ".$qwc." group by Z, N order by Z desc, N asc";
  $res = mysql_query($qry, $dbc);
  if( !$res ) { echo $qry; die("DB Issues. Sorry. (Error=CoR110)"); }
  $row = mysql_fetch_assoc($res);

  // Create strings for the search form.
  //   Default values are blank.
  //   Fields are wrapped with "%" (sql wildcard) to allow for prepended and appended spaces.
  $V2Bq = ($V2B == "Any") ? "any" : trim($V2B);
  $V3Bq = ($V3B == "Any") ? "any" : trim($V3B);
  $V4Bq = ($V4B == "Any") ? "any" : trim($V4B);
  $X_Fq = ($X_F == "Any") ? "any" : trim($X_F);
  $UIDq = ($UID == "Any") ? "any" : trim($UID);

  // Write out the table.
  for( $z=$zmx; $z>=0; $z-- ) {
    print "<tr><th>$z</th>";
    for( $n=0; $n<=$nmx; $n++ ) {
      //print "<td onmouseover=\"JavaScript: corH(this);\" onmouseout=\"JavaScript: corD(this);\">";
      print "<td>";
      if( $row['Z']==$z && $row['N']==$n ) {
        // If we only have one result, link directly to it.
        if( $row['T'] == 1 ) {
          print "<a href=\"details.php?ID=".$row['RunID']."\">";
        } else {
          print "<a href=\"search_results.php?Z=$z&N=$n&userID=$UIDq&V2B=$V2Bq&V3B=$V3Bq&V4B=$V4Bq&ExtF=$X_Fq\">";
        }
        print $row['T'];
        print "</a>";
        // Move to the next result row.
        $row = mysql_fetch_assoc($res);
      }
      print "</td>";
    }
    print "</tr>\n";
  }

  // Write a bottom row for neutron numbers.
  print "<tr>";
  print "<td></td>";
  for( $n=0; $n<=$nmx; $n++ ) { print "<th>$n</th>"; }
  print "</tr>\n";

  // Clean up.
  mysql_free_result( $res );
  $summary_message = $total_calculations_stored . " calculations in " . $total_runs_stored . " runs of " . $total_distinct_nuclei . " distinct nuclei.";
?>
</table>
</div>
<!--<div style="float: left; color: white;"><?php print $summary_message; ?></div>-->
<img src="image/neutron-arrow.png" align="center" />
<div style="float: right;"><em><?php print $summary_message; ?></em></div>
<form name="corForm" method="post" action="#ResultSummary">
  <input type="hidden" name="V2B" id="V2B" value="<?php print $V2B; ?>" />
  <input type="hidden" name="V3B" id="V3B" value="<?php print $V3B; ?>" />
  <input type="hidden" name="V4B" id="V4B" value="<?php print $V4B; ?>" />
  <input type="hidden" name="X_F" id="X_F" value="<?php print $X_F; ?>" />
  <input type="hidden" name="UID" id="UID" value="<?php print $UID; ?>" />
</form>
</div>
<!-- End "Chart of Results" -->
