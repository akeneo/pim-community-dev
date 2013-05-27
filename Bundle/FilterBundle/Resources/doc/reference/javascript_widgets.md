Javascript Widgets
------------------

On frontend side filter form types are represented by javascript widgets. 
They are located in Resources/public/js directory and use javascript namespace Oro.Filter.

###Table of Contents

- [Oro.Filter.List](#orofilterlist)
- [Oro.Filter.AbstractFilter](#orofilterabstractfilter)
- [Oro.Filter.TextFilter](#orofiltertextfilter)
- [Oro.Filter.ChoiceFilter](#orofilterchoicefilter)
- [Oro.Filter.NumberFilter](#orofilternumberfilter)
- [Oro.Filter.SelectFilter](#orofilterselectfilter)
- [Oro.Filter.MultiSelectFilter](#orofiltermultiselectfilter)
- [Oro.Filter.MultiSelectDecorator](#orofiltermultiselectdecorator)
- [Oro.Filter.DateFilter](#orofilterdatefilter)
- [Oro.Filter.DateTimeFilter](#orofilterdatetimefilter)
- [Example of Usage](#example-of-usage)
- [References](#references)

###Oro.Filter.List

Container for filters, renders all active filters, has a control to enable and disable filters

**Rendered As**

Combobox with Add button

**Parent**

_Backbone.View_

**Properties**

* filters: Object
* addButtonHint: String

**Properties Description**

* **filters** - named list of filters, instances of Oro.Filter.Abstract;
* **addButtonHint** - test of button that is used for adding filters to the list.


###Oro.Filter.AbstractFilter

Abstract filter that has common methods for all filters.

**Parent**

_Backbone.View_

**Properties**

* name: String
* label: String
* enabled: Boolean

**Properties Description**

* **name** - unique name name of filter;
* **label** - label of filter. This values is used for displaying filter in list options
and in rendering of filter html template;
* **enabled** - whether filter enabled or not. If filter is not enabled it will not be displayed in filter list.

###Oro.Filter.TextFilter

Has only one text input that can be filled by user. Operator type is not supported.

**Rendered As**

Clickable control with filter value hint.
When control is clicked a popup container with text input and update button is shown.

**Parent**

_Oro.Filter.AbstractFilter_

**Inherit Properties**

* name
* label
* enabled

###Oro.Filter.ChoiceFilter

This widget supports value input and operator type input.

**Rendered As**

Same as parent widget but also contains radio buttons for operator choices

**Parent**

_Oro.Filter.TextFilter_

**Options**

* choices: Object

**Inherit Properties**

* name
* label
* enabled

**Properties Description**

* **choices** - list of filter types (f.e. contains, not contains for text filter).

###Oro.Filter.NumberFilter

Filter that has an operator and additionally able to format value as a number (integer, decimal)

**Rendered As**

Same as parent widget but has behavior of parsing numbers as input value

**Parent**

_Oro.Filter.ChoiceFilter_

**Options**

* formatter: Oro.Filter.NumberFormatter
* formatterOptions: Object

**Inherit Properties**

* name
* label
* enabled
* choices

**Properties Description**

* **formatter** - instance of Oro.Filter.NumberFormatter, this object is responsible for converting string
to number and backward;
* **formatterOptions** - this value will be used as argument for Oro.Filter.NumberFormatter.
It contains next options:
    * decimals: Integer - number of decimals to display. Must be an integer;
    * decimalSeparator: String - the separator to use whendisplaying decimals;
    * orderSeparator: String - the separator to use to separator thousands. May be an empty string.

###Oro.Filter.SelectFilter

Filter that allows to select one of available values

**Rendered As**

Clickable control with filter value hint. When control is clicked a combobox with context search field
and available values is displayed.

**Parent**

_Oro.Filter.AbstractFilter_

**Options**

* options: Object
* contextSearch: Boolean

**Inherit Properties**

* name
* label
* enabled

**Properties Description**

* **options** - list of available options for select and multiselect filters.
* **contextSearch** - flag whether need to show context search field.

###Oro.Filter.MultiSelectFilter

Filter that allows to select any available values.

**Rendered As**

Same as parent, but several values can be selected.

**Parent**

_Oro.Filter.SelectFilter_

**Inherit Properties**

* name
* label
* enabled
* options
* contextSearch

###Oro.Filter.MultiSelectDecorator

Encapsulates additional logic related to select and multiselect widgets (filter list, select and multiselect filters).

**Option Parameters**

* **element** : HTML Element - HTML element used for rendering of multiselect widget;
* **parameters** : Object - list of parameters to initialize multiselect widget;
* **contextSearch** : Boolean - flag that specified whether to show context search field.

###Oro.Filter.DateFilter

Used for filtering date values.

**Rendered As**

Popup container has inputs for start and end dates. Each input is clickable calendar.
Available operators displayed as radio buttons.

**Parent**

_Oro.Filter.ChoiceFilter_

**Properties**

* typeValues
* externalWidgetOptions

**Inherit Properties**

* name
* label
* enabled
* choices

**Properties Description**

* **typeValues** - list of date/datetime type values for between/not between filter types;
* **externalWidgetOptions** - additional date/datetime widget options, gets from form type.

###Oro.Filter.DateTimeFilter

Used for filtering date time values.

**Rendered As**

Same as parent but clickable calendars also display controls for setting time

**Parent**

_Oro.Filter.DateFilter_

**Properties**

* typeValues
* externalWidgetOptions

**Inherit Properties**

* name
* label
* enabled
* choices
* typeValues
* externalWidgetOptions

###Example of Usage

Below is example of creating filter list:

```
var filtersList = new Oro.Filter.List({
    addButtonHint: '+ Add more',
    filters: {
        username: Oro.Filter.ChoiceFilter.extend({
            name:'username',
            label:'Username',
            enabled:true,
            choices:{"1": "contains", "2": "does not contain", "3": "is equal to"}
        }),
        gender: Oro.Filter.SelectFilter.extend({
            name:'gender',
            label:'gender',
            enabled:false,
            options: {"18": "Male", "19": "Female"}
        },
        salary: Oro.Filter.NumberFilter.extend({
            name:'salary',
            label:'salary',
            enabled:false,
            choices:{"1": "=", "2": ">", "3": "<"},
            formatterOptions: {"decimals": 0, "grouping": false, "orderSeparator": "", "decimalSeparator": "."}
        })
    }
});
$('#filter').html(filtersList.render().$el);
```

###References

* Backbone.js - http://backbonejs.org/
