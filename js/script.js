"use strict";
/**
*
*  do_it: add to the action
*  requires: object have array of requires fields to check it
*/

function ajaxSubmitForm(event, do_it, requires, container) {

//  debugger;

  var form = $(event.target);

  if (!validateForm(event, requires)) {
    event.preventDefault();
      return;
  }

  var url = form.action;

  var formData = form.serialize();

  formData = "_ajax_=1&" + formData;

  if (do_it) {
    formData = "do=" + do_it + "&" + formData;
  }

  $.ajax({
      type: form.attr('method'),
      url: form.attr('action'),
      data: formData,
      success: function(data) {
      if (container)
        $(container).empty().append(data);
      }
  });
  event.preventDefault();
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
  var form = $(id) || null;
  if (form !== null) {
    form.submit(function(event){
        if (!validateForm(event, requires))
          event.preventDefault();
      })
  }
  else {
    alert("Can not find " + id);
  }
}

function validateForm(event, requires) {

  if ((requires.fields) && (requires.fields.length > 0)) {

    var form = $(event.target);
    var err = true;
    requires.fields.forEach(function(field) {

      var f = form.find("#"+field);
      var v = $(f).val();

      if (!v) {
        f.addClass('required');
        err = false;
        event.preventDefault();
      }
      else
        f.removeClass('required');
    });
    var m = form.find('.message-panel');
    if (m) {
      if (!err) {
        m.addClass('required');
        m.empty().append('Error Message');
        m.show(200);
      }
      else {
        m.hide(200);
        m.removeClass('required');
        m.empty(200);
      }
    }
    return err;
  }
  else
    return true;
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