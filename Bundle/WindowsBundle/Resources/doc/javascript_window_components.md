JavaScript windows components
========================

In this article describe JavaScript components API for dialogs manipulations.

- [Extended jQuery UI dialog](#extended-jquery-ui-dialog)
    - [Options](#dialog-options)
    - [Methods](#dialog-methods)
    - [Example](#dialog-example)
- [DialogView Widget](#dialog-widget)
    - [Options](#dialog-widget-options)
    - [Methods](#dialog-widget-methods)
    - [Example](#dialog-widget-example)

<a name="extended-jquery-ui-dialog"></a>
### Extended jQuery UI dialog

Extended jQuery UI dialog add an maximize, minimize and collapse functionality to jQuery UI dialogs.

Oro dialog.extended is based on http://code.google.com/p/jquery-dialogextend/

Key difference is that Oro dialog.extended is a true extension of jQuery UI dialog and does not require to call
.dialogExtend() after .dialog(). To enable extended features only adding of options required. By default dialog logic is
same to jQuery UI dialog.

<a name="dialog-options"></a>
**Options**
<table>
<tr>
    <th>Option</th>
    <th>Default</th>
    <th>Type</th>
    <th>Description</th>
</tr>
<tr>
    <td>minimizeTo</td>
    <td>false</td>
    <td>selector, element</td>
    <td>Container for minimized windows.</td>
</tr>
<tr>
    <td>maximizedHeightDecreaseBy</td>
    <td>false</td>
    <td>integer, string</td>
    <td>Value on which height of maximized window will be decreased.
    minimize-bar may be used to decrease height by minimize bar height
    </td>
</tr>
<tr>
    <td>allowClose</td>
    <td>true</td>
    <td>bool</td>
    <td>Allow close functionality</td>
</tr>
<tr>
    <td>allowMaximize</td>
    <td>false</td>
    <td>bool</td>
    <td>Allow maximize functionality</td>
</tr>
<tr>
    <td>allowMinimize</td>
    <td>false</td>
    <td>bool</td>
    <td>Allow minimize functionality</td>
</tr>
<tr>
    <td>dblclick</td>
    <td>false</td>
    <td>bool, string</td>
    <td>Double click handler.
    Possible values "maximize", "minimize", "collapse"
    </td>
</tr>
<tr>
    <td>titlebar</td>
    <td>false</td>
    <td>bool, string</td>
    <td>Titlebar style
    Possible values false, 'transparent'
    </td>
</tr>
<tr>
    <td>icons</td>
    <td>{}</td>
    <td>object</td>
    <td>Icons settings</td>
</tr>
<tr>
    <td>snapshot</td>
    <td>null</td>
    <td>null, object</td>
    <td>Snapshot of state.</td>
</tr>
<tr>
    <td>state</td>
    <td>normal</td>
    <td>object</td>
    <td>State.
    Possible values maximized, minimized, normal, collapsed
    </td>
</tr>
<tr>
    <td colspan="4">Events</td>
</tr>
<tr>
    <td>beforeCollapse</td>
    <td>null</td>
    <td>null, callback</td>
    <td>Before collapse event handler</td>
</tr>
<tr>
    <td>beforeMaximize</td>
    <td>null</td>
    <td>null, callback</td>
    <td>Before maximize event handler</td>
</tr>
<tr>
    <td>beforeMinimize</td>
    <td>null</td>
    <td>null, callback</td>
    <td>Before minimize event handler</td>
</tr>
<tr>
    <td>beforeRestore</td>
    <td>null</td>
    <td>null, callback</td>
    <td>Before restore event handler</td>
</tr>
<tr>
    <td>collapse</td>
    <td>null</td>
    <td>null, callback</td>
    <td>On collapse event handler</td>
</tr>
<tr>
    <td>maximize</td>
    <td>null</td>
    <td>null, callback</td>
    <td>On maximize event handler</td>
</tr>
<tr>
    <td>minimize</td>
    <td>null</td>
    <td>null, callback</td>
    <td>On minimize event handler</td>
</tr>
<tr>
    <td>restore</td>
    <td>null</td>
    <td>null, callback</td>
    <td>On restore event handler</td>
</tr>
<tr>
    <td>stateChange</td>
    <td>null</td>
    <td>null, callback</td>
    <td>On state change event handler</td>
</tr>
</table>

<a name="dialog-methods"></a>
**Methods**
<table>
    <tr>
        <th>Method</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>maximize</td>
        <td>Maximize window</td>
    </tr>
    <tr>
        <td>minimize</td>
        <td>Minimize window</td>
    </tr>
    <tr>
        <td>collapse</td>
        <td>Collapse window</td>
    </tr>
    <tr>
        <td>restore</td>
        <td>Restore window</td>
    </tr>
    <tr>
        <td>snapshot</td>
        <td>Get snapshot</td>
    </tr>
    <tr>
        <td>state</td>
        <td>Get state</td>
    </tr>
    <tr>
        <td>actionsContainer</td>
        <td>Get actions container</td>
    </tr>
    <tr>
        <td>showActionsContainer</td>
        <td>Show actions container</td>
    </tr>
</table>

<a name="dialog-example"></a>
**Example**
``` javascript
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="{{ asset('bundles/orowindows/css/jquery.dialog.extended.css') }}" media="all" />
<script type="text/javascript" src="{{ asset('bundles/orowindows/js/jquery.dialog.extended.js') }}"></script>
<script type="text'javascript">
$(function(){
  $("#my-button").click(function(){
    $("<div>This is  content</div>")
      .dialog({
        "appendTo": $("#dialogContainer"),
        "minimizeTo": $("#minimizeBar"),
        "maximizedHeightDecreaseBy": "minimize-bar",
        "title" : "This is dialog title",
        "buttons" : { "OK" : function(){ $(this).dialog("close"); } }
        "allowClose" : true,
        "allowMaximize" : true,
        "allowMinimize" : true,
        "dblclick" : "collapse",
        "titlebar" : "transparent",
        "icons" : {
          "close" : "ui-icon-circle-close",
          "maximize" : "ui-icon-circle-plus",
          "minimize" : "ui-icon-circle-minus",
          "restore" : "ui-icon-bullet"
        },
        "beforeCollapse" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "beforeMaximize" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "beforeMinimize" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "beforeRestore" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "collapse" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "maximize" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "minimize" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "restore" : function(evt, dlg){ alert(evt.type+"."+evt.handleObj.namespace); },
        "stateChange": function(evt, data) {alert(data.state);}
      });
  });
});
</script>
```
<a name="dialog-widget"></a>
### DialogView Widget

DialogView widget responsibilities:
- Dialog content handling including AJAX content loading
- Embedding form into dialogs with AJAX
- State Save and restore functionality
- Multiple dialogs placement

<a name="dialog-widget-options"></a>
**Options**
<table>
<tr>
    <th>Option</th>
    <th>Default</th>
    <th>Type</th>
    <th>Description</th>
</tr>
<tr>
    <td>actionsEl</td>
    <td>'.widget-actions'</td>
    <td>selector, element</td>
    <td>Form action element to adopt as dialog actions</td>
</tr>
<tr>
    <td>dialogOptions</td>
    <td>null</td>
    <td>null, Dialog Options object</td>
    <td>Dialog Options</td>
</tr>
<tr>
    <td>url</td>
    <td>false</td>
    <td>false, string</td>
    <td>Dialog content URL</td>
</tr>
<tr>
    <td>elementFirst</td>
    <td>true</td>
    <td>bool</td>
    <td>Render content at first run if any, load remote content only on next tries</td>
</tr>
<tr>
    <td>el</td>
    <td>null</td>
    <td>selector, element</td>
    <td>Dialog element</td>
</tr>
</table>

<a name="dialog-widget-methods"></a>
**Methods**
<table>
    <tr>
        <th>Method</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>render</td>
        <td>Render dialog</td>
    </tr>
    <tr>
        <td>close</td>
        <td>Close dialog</td>
    </tr>
    <tr>
        <td>getWidget</td>
        <td>Get dialog UI widget instance</td>
    </tr>
    <tr>
        <td>loadContent</td>
        <td>Load remote content</td>
    </tr>
</table>

<a name="dialog-widget-example"></a>
**Example**
``` javascript
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="{{ asset('bundles/orowindows/css/jquery.dialog.extended.css') }}" media="all" />
<script type="text/javascript" src="{{ asset('bundles/orowindows/js/jquery.dialog.extended.js') }}"></script>

<script type="text/javascript" src="{{ asset('bundles/oroui/lib/underscore.js') }}"></script>
<script type="text/javascript" src="{{ asset('bundles/oroui/lib/backbone.js') }}"></script>

<script type="text/javascript" src="{{ asset('bundles/orowindows/js/views/dialog.js') }}"></script>
<script type="text/javascript" src="{{ asset('bundles/orowindows/js/models/state.js') }}"></script>
<script type="text/javascript" src="{{ asset('bundles/orowindows/js/collections/state.js') }}"></script>
<script type="text/javascript" src="{{ asset('bundles/orowindows/js/views/dialog.js') }}"></script>
<script type="text/javascript">
$(function() {
$("#my-button").click(function() {
    new Oro.widget.DialogView({
        el: $("<div>Some text if you need will be here</div>"),
        url: '/dialog/content',
        elementFirst: true,
        dialogOptions: {
            allowMaximize: true,
            allowMinimize: true,
            dblclick: 'maximize',
            title: "dialog window title",
            width: 400,
            height: 400
       }
   }).render();
});
});
</script>
```
