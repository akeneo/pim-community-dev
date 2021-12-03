
var myTextarea = document.getElementById('json-editor');
var editor = CodeMirror.fromTextArea(myTextarea, {
  lineNumbers: true,
  lineWrapping: true
});
