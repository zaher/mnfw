/*
*  @ref: http://www.nczonline.net/blog/2009/07/28/the-best-way-to-load-external-javascript/
*/
function loadScript(name, url, callback = null){

    var script = document.createElement("script")
    script.type = "text/javascript";
    script.name = name;
    script.language = "javascript";

    if (script.readyState){  //IE
        script.onreadystatechange = function(){
            if (script.readyState == "loaded" ||
                    script.readyState == "complete"){
                script.onreadystatechange = null;
                callback();
            }
        };
    } else {  //Others
        script.onload = function(){
            callback();
        };
    }

    script.src = url;
    document.getElementsByTagName("head")[0].appendChild(script);
}

function itemShowHide(id){
  if(document.getElementById(id).style.display=='')
    document.getElementById(id).style.display='none';
  else
    document.getElementById(id).style.display='';
}
