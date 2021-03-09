<!-- Begin "Chart of Results" -->
<?php include "../include/dbauth.php"; ?>
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
  $V2B = !isset($_POST['V2B']) ? 'Any' : trim($_POST['V2B']);
  $V3B = !isset($_POST['V3B']) ? 'Any' : trim($_POST['V3B']);
  $V4B = !isset($_POST['V4B']) ? 'Any' : trim($_POST['V4B']);
  $X_F = !isset($_POST['X_F']) ? 'Any' : trim($_POST['X_F']);
  $UID = !isset($_POST['UID']) ? 'Any' : trim($_POST['UID']);

  function corLink($fField, $fValue) {
    return "<a href=\"JavaScript: corSet('".$fField."','".$fValue."')\">".trim($fValue)."</a>";
  }

  function genPotentialRow($fField, $pField, $pSelected, $pTitle, $dbh) {
    $qry = "select ".$pField." from RUN group by ".$pField;
    $res = simple_query($dbh, $qry);
    if( $res ) {
      if( count($res) == 0 ) {
        // Do nothing.
      } elseif( count($res) == 1) {
        $row = $res[0];
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
        foreach($res as $row) {
          // We don't need to print the same line twice.
          if( $row[$pField] != $pSelected ) {
            print "&nbsp;&nbsp;" . corLink($fField,$row[$pField]);
          }
        }
      }
    }
    print "</td></tr>\n";
  }

  genPotentialRow("V2B", "2B_potential", $V2B, "2 Body Potential", $dbh);
  genPotentialRow("V3B", "3B_potential", $V3B, "3 Body Potential", $dbh);
  genPotentialRow("V4B", "4B_potential", $V4B, "4 Body Potential", $dbh);

  print "<tr><td><strong>External Field:</strong></td>";
  $qry = "select ext_field from RUN group by ext_field";
  $res = simple_query($dbh, $qry);
  if( $res ) {
    print "<td colspan=2><select onChange=\"JavaScript: corSetChange('X_F',this);\" id=\"extFieldSelect\" style=\"width:380px\">";
    print "<option value=\"".$X_F."\" selected><em>".$X_F."</em></option>";
    print "<option value=\"Any\"><em>Any</em></option>";
    foreach($res as $row) {
      print "<option value=\"". $row['ext_field']. "\">" . $row['ext_field'] . "</option>";
    }
  }
  print "</select></td></tr>\n";

  print "<tr><td><strong>User:</strong></td>";
  $qry = "select u.name, u.id from RUN r join USERS u on r.username = u.username group by name, id order by name, id";
  $res = simple_query($dbh, $qry);
  if( $res ) {
    print "<td colspan=2><select onChange=\"JavaScript: corSetChange('UID',this);\" id=\"userSelect\" style=\"width:380px\">";
    print "<option value=\"Any\"><em>Any</em></option>";
    foreach($res as $row) {
      if( $row['id'] == $UID ) { $optString = "selected"; } else { $optString = ""; }
      print "<option value=\"". $row['id']. "\"". $optString .">" . $row['name'] . "</option>";
    }
  }
  print "</td></tr>\n";
?>
</table>
<div>
<img src="image/proton-arrow.png" style="float: left; vertical-align: middle;" />
<table class="cor">
<?php
  // Let's get some statistics on the number of runs and calculations.
  $qry = "select (select count(*) from RUN) as numRuns, (select count(*) from RES_FILE) as numCalcs";
  $res = simple_query($dbh, $qry);
  $row = $res[0];
  $total_calculations_stored = $row['numCalcs'];
  $total_runs_stored = $row['numRuns'];

  // More statistics...number of different nuclei.
  $qry = "select Z, N, count(*) as T from RUN group by Z, N order by Z desc, N asc";
  $res = simple_query($dbh, $qry);
  $total_distinct_nuclei = count($res);

  // Check how big the table needs to be.
  $qry = "select max(Z) as maxZ, max(N) as maxN from RUN";
  $res = simple_query($dbh, $qry);
  $row = $res[0];
  $zmx = $row['maxZ'];
  $nmx = $row['maxN'];

  // Actual result counts.
  $qwc = " where 1=1 "; // Query "Where" Clause...
  if( $V2B != "Any" ) { $qwc .= " and 2B_potential = '".$V2B."'"; }
  if( $V3B != "Any" ) { $qwc .= " and 3B_potential = '".$V3B."'"; }
  if( $V4B != "Any" ) { $qwc .= " and 4B_potential = '".$V4B."'"; }
  if( $X_F != "Any" ) { $qwc .= " and ext_field = '".$X_F."'"; }
  if( $UID != "Any" ) { $qwc .= " and username in (select username from USERS where id = ".$UID.") "; }
  $qry = "select Z, N, count(*) as T, max(runID) as RunID from RUN ".$qwc." group by Z, N order by Z desc, N asc";
  $res = simple_query($dbh, $qry);
  $rec = 0;
  $row = $res[$rec];

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
        if( $rec < count($res) ) {
          $rec+=1;
          $row = $res[$rec];
        }
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
