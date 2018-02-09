/* JavaScript for DBMS */

function highlightTR(anElement) {
  anElement.className = "trHighlight";
}

function dehighlightTR(anElement) {
  anElement.className = "trNormal";
}

function goID(anID) {
  self.location.href="details.php?ID=" + anID;
}

function popup(window) {
window.open( window, "myWindow", 
"status = 1, height = 300, width = 300, resizable = 0" )
}



// EOF
