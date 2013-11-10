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

function ajaxSubmitForm(form, event, container) {

  event.preventDefault();

  var url = event.currentTarget.action;

  var formData = form.serialize();

  formData += "&_ajax_=1";

  var posting = $.post(
    url,
    formData
  );

  posting.done(function(data) {
//    if (container !== undefined)
      $(container).empty().append(data);
  });
}

function ajaxAttachForm(id, container) {
//  document.getElementByName(id).onclick = ajaxSubmitForm;
  form = $(id);
  form.submit(function(event){
      ajaxSubmitForm(form, event, container);
    })
}


function validateForm(form, fields) {

}

function showFormError(form, error) {

}