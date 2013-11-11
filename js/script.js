"use strict";
/**
*
*  do_it: add to the action
*  requires: object have array of requires fields to check it
*/

function ajaxSubmitForm(event, do_it, requires, container)
{
  event.preventDefault();

  var form = $(event.target);

  if (requires.fields.length > 0) {
    validateForm(event, requires);
  }
  alert("ajaxSubmitForm");

  var url = form.action;

  var formData = form.serialize();

  formData = "_ajax_=1&" + formData;

  if (do_it) {
    formData = "do=" + do_it + "&" +formData;
  }

  $.ajax({
      type: form.method,
      url: form.action,
      data: formData,
      success: function(data) {
      if (container)
        $(container).empty().append(data);
      }
  });
}

function ajaxAttachForm(id, do_it, requires, container) {
  var form = $(id) || null;
  if (form !== null) {
    form.submit(function(event){
        ajaxSubmitForm(event, do_it, requires, container);
      })
  }
  else {
    alert("Can not find " + id);
  }
}

function attachForm(id, requires) {
//  document.getElementByName(id).onclick = ajaxSubmitForm;
  var form = $(id) || null;
  if (form !== null) {
    form.submit(function(event){
        validateForm(event, requires);
      })
  }
  else {
    alert("Can not find " + id);
  }
}

function validateForm(event, requires) {
  alert("validateForm");
  $.each(requires.fields, function(i, field) {
    if ($(field).val.length == 0) {
      alert(field.name + ' is required');
      event.preventDefault();
    }
  })
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