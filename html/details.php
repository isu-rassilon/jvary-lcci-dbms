<?php
  // If there is no sane RunID, complain.
  $RunID = 0;
  if( !isset($_GET['ID']) ) { die("Bad link. Did not identify the desired run."); }
  else {
    if( !is_numeric($_GET['ID']) ) { die("Bad link. Passed run identifier was corrupt."); }
    else { $RunID = mysql_escape_string($_GET['ID']); } // escape just in case (for safety).
  }

  // We'll need to access the database.
  include "../include/dbauth.php";
  $dbc = mysql_connect($db_host, $dbu_name, $dbu_pass);

  // Does this RunID actually exist?
  $qry = "select r.*, u.name, pt.symbol as nuclei_symbol, pt.name as nuclei_name, (Z+N) as A, timediff(END_DATE,START_DATE) as time_taken from lcci.RUN r join lcci.USERS u on r.username = u.username join lcci.periodic_table pt on ifnull(pt.protons,r.Z)=r.Z and ifnull(pt.neutrons,r.N)=r.N where r.runID = " . $RunID . " order by override desc";
  $res = mysql_query($qry, $dbc);
  if( !$res ) { die("Sorry, an internal issue occured. (msg=det100)"); }
  if( mysql_num_rows($res) <= 0 ) {
    die("Sorry, the link provides references a run that doesn't appear to exist.");
  }
  $run = mysql_fetch_assoc($res); // We'll be using the results in the page.

  // Include the HTML lead-in.
  $htmlTitle = $run['A'].$run['nuclei_symbol']." - DBMS Details for Run (ID=".$RunID.")"; // This changes the default title.
  include "../include/htmlhead.php";

  // Include the php custom functions.
  include "../include/functions.php";
?>

<style type="text/css">
.nucleiBox { border: 2px black solid; width: 100px; height: 100px; }
.rowLayout { width: 100%; display: block; }
.rowLeft { float: left; }
.rowRemainder { float: left; clear: right; width: *; }
.clearFloat { clear: both; }
.secTitle { text-decoration: underline; font-weight: bold; }
.vSpace { height: 10px; width: 100%; display: block; clear: both; }
.hSpace { height: 10px; width: 20px; float: left; }
.tpi { position: relative; left: 10px; }
.runCalcs { margin: 0px 0px 0px 0px; }
.runCalcs li { margin-left: 0px; }
.dirCat { font-style: italic; }
</style>

<script language="JavaScript" type="text/javascript">
  jQuery(document).ready(function(){
    $('#multiOpenAccordion').multiAccordion({active: [0]});
  });
</script>

<!-- Most important data first! -->
<div class="rowLayout">
  <div class="rowLeft">
    <table class="nucleiBox">
      <tr><td>
        <table align="center">
          <tr><td align="right"><?php print $run['A']; ?></td><td></td><td></td></tr>
          <tr><td></td><td style="font-size: xx-large;"><?php print $run['nuclei_symbol']; ?></td><td></td></tr>
          <tr><td align="right"><?php print $run['Z']; ?></td><td></td><td align="left"><?php print $run['N']; ?></td></tr>
        </table>
      </td></tr>
    </table>
    <div align="center"><a title="Look this up at the National Nuclear Data Center." target="_blank" href="http://www.nndc.bnl.gov/nudat2/reCenter.jsp?z=<?php print $run['Z']."&n=".$run['N']; ?>"><em><?php print $run['nuclei_name']; ?></em></a></div>
  </div>
  <!-- hSpace width decreased because it just looked too big.  -->
  <div class="hSpace" style="width: 10px;"></div>
  <div class="rowRemainder">
    <div><span class="secTitle">Number of states calculated</span>: <?php print $run['Nstates']; ?></div>
    <?php if(is_numeric(trim($run['refstate']))) { print "<div><span class=\"secTitle\">Initial state for transitions</span>: #".trim($run['refstate'])."</div>"; } ?>
    <div class="vSpace"></div>
    <div class="rowLeft">
      <div class="secTitle">Potentials</div>
      <div class="tpi">2 Body: <?php print $run['2B_potential']; ?></div>
      <?php if(trim($run['3B_potential']) != "NONE") { print "<div class=\"tpi\">3 Body: ".$run['3B_potential']."</div>"; } ?>
      <?php if(trim($run['4B_potential']) != "NONE") { print "<div class=\"tpi\">4 Body: ".$run['4B_potential']."</div>"; } ?>
    </div>
    <!-- hSpace width increased by 10px to account for tpi. -->
    <div class="hSpace" style="width: 30px;"></div>
    <div class="rowLeft">
      <div><span class="secTitle">External Field</span>: <?php print $run['ext_field']; ?></div>
      <div><span class="secTitle">Parity</span>: <?php print parityText($run['parity']); ?></div>
      <!-- Only print J (total angular momentum) if the run is a "total-J" run (twiceJ!=-1). -->
      <?php if( $run['twiceJ'] != -1 ) { print "<div><span class=\"secTitle\">J</span>: ".twiceFieldToFraction($run['twiceJ'])."</div>\n"; } ?>
      <div><span class="secTitle">m<sub>j</sub></span>: <?php print twiceFieldToFraction($run['twiceMj']); ?></div>
    </div>
    <div class="hSpace"></div>
    <div class="rowRemainder">
      <div><span class="secTitle">Nmax</span>: <?php print $run['Nmax']; ?></div>
      <div class="secTitle">Nshell</div>
      <div class="tpi">Z: from <?php print $run['nshell_min_Z'] . " to " . $run['nshell_max_Z']; ?></div>
      <div class="tpi">N: from <?php print $run['nshell_min_N'] . " to " . $run['nshell_max_N']; ?></div>
    </div>
  </div>
</div>
<div class="vSpace"></div>

<!-- Slightly less important data second. -->
<div class="rowLayout">
  <div class="rowRemainder">
    <div class="secTitle">Additional Quantities Calculated</div>
    <ul class="runCalcs">
    <?php
      $resData = "E";
      if( $run['J'] == 'Y' )      { $resData .= ",J";      print "<li><a href=\"#results\">J - Total Angular Momentum</a></li>\n"; }
      if( $run['T'] == 'Y' )      { $resData .= ",T";      print "<li><a href=\"#results\">T - Isospin</a></li>\n"; }
      if( $run['radius'] == 'Y' ) { $resData .= ",radii";  print "<li><a href=\"#results\">radii - RMS Radii</a></li>\n"; }
      if( $run['Hcm'] == 'Y' )    { $resData .= ",Hcm";    print "<li><a href=\"#results\">H<sub>cm</sub> - Energy of the Center of Mass</a></li>\n"; }
      if( $run['obdme'] == 'Y' )  { print "<li><a href=\"#obdme\">obdme - One Body Density Matrix Elements</a></li>\n"; }
      if( $run['smwf'] == 'Y' )   { print "<li><a href=\"#smwf\">smwf - (Shell Model) Wave Functions</a></li>\n"; }
      $resData .= " - ";
    ?></ul>
  </div>
  <div class="clearFloat"></div>
</div>
<div class="vSpace"></div>

<!-- Comments? -->
<?php
  // If a user left a comment, it's probably very important.
  if( trim($run['COMMENTS']) != "" ) {
    $clen = strlen(trim($run['COMMENTS']));
    print "<div class=\"secTitle\">User Comment(s)</div>\n";
    print "<textarea rows=".ceil($clen/72)." cols=72 readonly=true style=\"border: none;\">".trim($run['COMMENTS'])."</textarea>";
    print "<div class=\"vSpace\"></div>\n";
  }
?>

<div id="multiOpenAccordion">
<!-- We've got their attention so print the credits (who, where, when) -->
<!--div class="rowLayout"-->
  <!--div class="secTitle">Execution Details</div-->
  <h3><a href="#">Execution Details</a></h3>
  <div>
  <table>
  <tr><td>User@Machine:</td><td>&nbsp;</td><td><?php print $run['name']." @ ".$run['machineID']; if( trim($run['jobID']) != "" ) { print " (JobID: ".$run['jobID'].")"; } ?></td></tr>
  <tr><td>Working Folder:</td><td>&nbsp;</td><td><?php print $run['rundir']; ?></td></tr>
  <tr><td>Running Time:</td><td>&nbsp;</td><td><?php print $run['time_taken']." (Started: ".$run['START_DATE']."; Ended: ".$run['END_DATE'].")"; ?>.</td></tr>
  </table>
  </div>
<!--div class="vSpace"></div-->

  <!-- Data file locations -->
  <h3><a href="#">General Data Files</a></h3>
  <div>
  <!--div class="secTitle">General Data Files</div-->
  <table>
  <!--<tr><td>DBMS:</td><td>&nbsp;</td><td><?php print "<a title=\"Click here for the original raw file that created this entry.\" href='raw/".$run['info_file']."'>".$run['info_file']."</a>"; ?></td></tr>-->
  <tr><td>DBMS:</td><td>&nbsp;</td><td><?php print $run['info_file']; ?></td></tr>
  <tr><td>Output:</td><td>&nbsp;</td><td><?php print $run['output_file']; ?></td></tr>
  <tr><td>MBSI:</td><td>&nbsp;</td><td><?php print $run['mbsi_file']; ?></td></tr>
  <tr><td>Init:</td><td>&nbsp;</td><td><?php print $run['INIT_FILE']; ?></td></tr>
  </table>
  </div>
  <!--div class="vSpace"></div-->

  <?php
    function displayDetailFiles($dbc, $anchor, $dtree, $desc, $sql) {
      $ddf = mysql_query($sql, $dbc);
      if( !$ddf ) {
        print $sql;
        print "<!-- No ".$fDesc." -->\n";
      } else {
        $lunset = ": unset :";
        $ldir = $lunset;
        if( mysql_num_rows($ddf) > 0 ) {
          //print "<div class=\"secTitle\"><a name=\"".$anchor."\"></a>".$desc."</div>\n";
          print "<h3><a href=\"#\">".$desc."</a></h3>";
          print "<div>";
          print "<ul class=\"runCalcs\">\n";
          while( $out = mysql_fetch_assoc($ddf) ) {
            if( $dtree && $ldir != dirname($out['fspec']) ) {
              if( $ldir != $lunset ) { print "</li></ul>\n"; }
              $ldir = dirname($out['fspec']);
              print "<li><strong>Directory</strong>: <span class=\"dirCat\">".$ldir."</span>\n";
              print "<ul>\n";
            }
            if( $dtree ) {
              print "<li><!-- ".$out['id']." --> ".basename($out['fspec'])."</li>\n";
            } else {
              print "<li><!-- ".$out['id']." --> ".$out['fspec']."\n";
            }
          }
          if( $dtree && $ldir != $lunset ) { print "</ul>\n"; }
          print "</li></ul></div>\n";
          //print "<div class=\"vSpace\"></div>\n";
        }
      }
      mysql_free_result($ddf);
    }

    displayDetailFiles( $dbc, "results", true, $resData."Result Files", "select ID as id, filename as fspec from lcci.RES_FILE where runID = ".$RunID." order by fspec" );
    displayDetailFiles( $dbc, "obdme", true, "OBDME - One Body Density Matrix Elements Files", "select ID as id, filename as fspec from lcci.OBDME_FILE where runID = ".$RunID." order by fspec" );
    displayDetailFiles( $dbc, "smwf", true, "SMWF - (Shell Model) Wave Function Files", "select ID as id, filename as fspec from lcci.SMWF_FILE where runID = ".$RunID." order by fspec" );
    displayDetailFiles( $dbc, "hamdir", false, "Hamiltonian Directories", "select ID as id, path as fspec from lcci.HAMDIR where runID = ".$RunID." order by fspec" );
  ?>
</div>

<?php
  // Tell the DB we are done with these results and it.
  mysql_free_result($res);
  mysql_close($dbc);

  // Finally end the page.
  include "../include/htmltail.php";
?>
