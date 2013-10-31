Twig Extensions
---------------

OroFilterBundle provides a Twig extension that declares functions that can be used
to render view objects of filters forms as HTML and javascript code.

###Table of Contents

- [oro_filter_render_filter_javascript Function](#oro_filter_render_filter_javascript-function)
- [oro_filter_render_header_javascript Function](#oro_filter_render_header_javascript-function)
- [oro_filter_render_header_stylesheet Function](#oro_filter_render_header_stylesheet-function)


###oro_filter_render_filter_javascript Function

**Arguments**

* filter form type view object

**Result**

String that actually is a javascript code that creates a widget that represents filter form type on frontend.

**Example of Usage**

```
<script type="text/javascript">
    var nameFilter = {{ oro_filter_render_filter_js(filter_form.children['name']) }};
</script>
```

This equals to next code:

```
<script type="text/javascript">
    var nameFilter = new (Oro.Filter.ChoiceFilter.extend({
        'name':    'name',
        'label':   'Name',
        'enabled': true,
        'choices': {1: 'contains', 2: 'not contains', 3: 'equal'}
    }));
</script>
```


###oro_filter_render_header_javascript Function

Renders HTML that includes all required javascript files.

**Example of Usage**

```
{{ oro_filter_render_header_javascript() }}
```

Equals to something like this:

```
{% javascripts
    {# list of references to js files #}
    ...
    output='js/oro.filter.js'
%}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```


###oro_filter_render_header_stylesheet Function

Renders HTML that includes all required CSS files.

**Example of Usage**

```
{{ oro_filter_render_header_stylesheet() }}
```

Equals to something like this:

```
{% stylesheets
    {# list of references to css files #}
    ...
    output='css/oro.filter.css'
%}
    <link rel="stylesheet" type="text/css" href="{{ asset_url }}" />
{% endstylesheets %}
```
