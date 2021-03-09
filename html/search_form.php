<?php
  $htmlTitle = "DBMS Search";
  include "../include/htmlhead.php";
  include "../include/dbauth.php";
?>
<script language="JavaScript" type="text/javascript">
  function obsToggle(fid) {
    var cbox = document.getElementById(fid);
    cbox.checked = !cbox.checked;
  }

  jQuery(document).ready(function(){
    $('#multiOpenAccordion').multiAccordion({active: [0,1]});
  });
</script>

<div><h3>Search for Calculations</h3></div>
<form name="searchForm" action="search_results.php" method="post">

<div id="multiOpenAccordion">
  <h3><a href="#">Common Search Parameters</a></h3>
  <div>
  <div style="float: left; width: 8em;">Protons (Z)</div>
  <div style="float: left; width: 12em;"><input type="text" name="Z" value="" size="6" /></div>
  <div style="float: left; width: 8em;">Neutrons (N)</div>
  <div style="float: left; width: 12em;"><input type="text" name="N" value="" size="6" /></div>
  <div style="clear: both; height: 5px; width: 100%;"></div>
<?php
  $Vc = 0;
  function genPotentialRow($fField, $pField, $pTitle, $dbh, $Vc) {
    $qry = "select ".$pField." from RUN group by ".$pField;
    $res = simple_query($dbh, $qry);
    if( $res ) {
      if( count($res) > 1 ) {
        print "<div style=\"float: left; width: 8em;\">".$pTitle."</div>";
        print "<div style=\"float: left; width: 12em;\">";
        print "<select name=\"".$fField."\">";
        print "<option value=\"any\">Not Specified</option>";
        foreach($res as $row) {
          print "<option value=\"".$row[$pField]."\">".$row[$pField]."</option>";
        }
        print "</select>";
        print "</div>";

        // Vc makes sure no more than two potentials are on a row.
        $Vc++;
        if($Vc % 2 == 0) {
          print "<div style=\"clear: both; height: 5px; width: 100%;\"></div>";
        }
      }
    }
    return $Vc;
  }

  $Vc = genPotentialRow("V2B", "2B_potential", "2 Body Potential", $dbh, $Vc);
  $Vc = genPotentialRow("V3B", "3B_potential", "3 Body Potential", $dbh, $Vc);
  $Vc = genPotentialRow("V4B", "4B_potential", "4 Body Potential", $dbh, $Vc);
  $Vc = genPotentialRow("ExtF", "ext_field", "External Field", $dbh, $Vc);
?>
  </div>
  <h3><a href="#">Calculated Observables</a></h3>
  <div>
    <div style="float: left; width: 20em;">
      <div style="float: left; width: 12em;">Using reference state #</div>
      <div style="float: left; width: 8em;"><input type="text" name="refstate" value="" size="6" /></div>
      <div style="clear: both; height: 5px; width: 100%;"></div>
      <div style="float: left; width: 12em;">Number of states</div>
      <div style="float: left; width: 8em;"><input type="text" name="nstates" value="" size="6" /></div>
    </div>
    <div style="float: left; width: 20em;">
      <span style="text-decoration: underline;">Other Required Observables</span>
      <ul style="list-style-type: none; padding-left: 0px; margin-left: 0px; margin-top: 0px; margin-bottom: 0px;">
        <li><input type="checkbox" name="obsJ" id="obsJ" value="y" /><span onclick="obsToggle('obsJ');"> J - Total Angular Momentum</span></li>
        <li><input type="checkbox" name="obsT" id="obsT" value="y" /><span onclick="obsToggle('obsT');"> T - Isospin</span></li>
        <li><input type="checkbox" name="obsOBDM" id="obsOBDM" value="y" /><span onclick="obsToggle('obsOBDM');"> One Body Density Matrices</span></li>
        <li><input type="checkbox" name="obsSMWF" id="obsSMWF" value="y" /><span onclick="obsToggle('obsSMWF');"> Wave Functions</span></li>
      </ul>
    </div>
  </div>
  <h3><a href="#">Run Details</a></h3>
  <div>
<?php
  // Display Users List.
  $qry = "select u.name, u.id from RUN r join USERS u on r.username = u.username group by name, id order by name, id";
  $res = simple_query($dbh, $qry);
  if( $res ) {
    print "<div style=\"float: left; width: 8em;\">Username</div>";
    print "<div style=\"float: left; width: 12em;\">";
    print "<select name=\"userID\">";
    print "<option value=\"any\">Any</option>";
    foreach($res as $row) {
      print "<option value=\"". $row['id']. "\">" . $row['name'] . "</option>";
    }
    print "</select>";
    print "</div>";
  }

  // Display Machines List.
  $qry = "select distinct machineID from RUN order by machineID";
  $res = simple_query($dbh, $qry);
  if( $res ) {
    print "<div style=\"float: left; width: 8em;\">Machine</div>";
    print "<div style=\"float: left; width: 12em;\">";
    print "<select name=\"machineID\">";
    print "<option value=\"any\">Any</option>";
    foreach($res as $row) {
      print "<option value=\"". $row['machineID']. "\">" . $row['machineID'] . "</option>";
    }
    print "</select>";
    print "</div>";
  }
?>
    <div style="clear: both; height: 5px; width: 100%;"></div>
    <div style="float: left; width: 8em;">Nmax</div>
    <div style="float: left; width: 12em;"><input type="text" name="nmax" value="" size="6" /></div>
    <div style="float: left; width: 12em;">m<sub>J</sub> (multiplied by two)</div>
    <div style="float: left; width: 8em;"><input type="text" name="mj" value="" size="6" /></div>
    <div style="clear: both; height: 5px; width: 100%;"></div>
    <div style="float: left; width: 28em;">For a total-J run, enter the J value (multiplied by two)</div>
    <div style="float: left; width: 8em;"><input type="text" name="totalJ" value="" size="6" /></div>
    <div style="clear: both; height: 5px; width: 100%;"></div>
    <div style="float: left; width: 8em;">Nshell min Z</div>
    <div style="float: left; width: 12em;"><input type="text" name="ns_min_z" value="" size="6" /></div>
    <div style="float: left; width: 8em;">Nshell min N</div>
    <div style="float: left; width: 12em;"><input type="text" name="ns_min_n" value="" size="6" /></div>
    <div style="clear: both; height: 5px; width: 100%;"></div>
    <div style="float: left; width: 8em;">Nshell max Z</div>
    <div style="float: left; width: 12em;"><input type="text" name="ns_max_z" value="" size="6" /></div>
    <div style="float: left; width: 8em;">Nshell max N</div>
    <div style="float: left; width: 12em;"><input type="text" name="ns_max_n" value="" size="6" /></div>
  </div>
  <h3><a href="#">Internal Identifiers</a></h3>
  <div>
    <div style="float: left; width: 7em;">Run ID Number</div>
    <div style="float: left; width: 13em;"><input type="text" name="runID" value="" size="6" /></div>
    <div style="float: left; width: 10em;">DBMS Info File Name</div>
    <div style="float: left; width: 28em;"><input type="text" name="info_file" value="" size="28" /></div>
  </div>
</div>
<div>
  <div style="clear: both; height: 5px; width: 100%;"></div>
  <input type="submit" name="submit" value="Search" style="width: 16em; font-size: large;" />
  <input type="reset" name="reset" value="Reset Form" style="width: 10em; font-size: large;" />
</div>
</form>
<?php include "../include/htmltail.php"; ?>
