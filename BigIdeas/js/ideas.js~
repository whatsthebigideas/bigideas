function loadDoc() {
  var url = ideas_js.ideas_xml_url;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      createTable(this);
    }
  };
  xhttp.open("GET", url, true);
  xhttp.send();
}

function createTable(xml) {
  var newIdeaPage = ideas_js.new_idea_page;
  var i;
  var xmlDoc = xml.responseXML;
  var table="<tr><td colspan='2'> <a href=" + newIdeaPage + "/new-idea>Have an Idea? Start a group to work on it!</a></td></tr><tr><th>Idea</th><th>Donate</th><th>Description</th></tr>";
  var x = xmlDoc.getElementsByTagName("idea");
  for (i = 0; i <x.length; i++) {
    table += "<tr><td>" +
    x[i].getElementsByTagName("title")[0].childNodes[0].nodeValue +
    "</td><td>" +
    x[i].getElementsByTagName("donate")[0].childNodes[0].nodeValue +
    "</td><td>" +
    x[i].getElementsByTagName("description")[0].childNodes[0].nodeValue +
    "</td></tr>";
  }
  document.getElementById("ideas-table").innerHTML = table;
}
