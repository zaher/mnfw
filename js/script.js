"use strict";

function ajaxSubmitForm(form, event, do, container) {

  var url = form.action;

  var formData = form.serialize();

  formData = "_ajax_=1&" + formData;
  if (do !== undefined) {
    formData = "do=" + do + formData;
  }

  $.ajax({
      type: form.method,
      url: form.action,
      data: formData,
      success: function(data) {
        $(container).empty().append(data);
      }
  });

  event.preventDefault();
}

function ajaxAttachForm(id, do, container) {
//  document.getElementByName(id).onclick = ajaxSubmitForm;
  var form = $(id) || null;
  if (form !== null) {
    form.submit(function(event){
        ajaxSubmitForm(form, event, do, container);
      })
  }
  else {
    alert("Can not find " + id);
  }
}


function validateForm(form, fields) {

}

function showFormError(form, error) {

}

/*
*  @ref: http://www.nczonline.net/blog/2009/07/28/the-best-way-to-load-external-javascript/
*/
function loadScript(name, url, callback) {

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
