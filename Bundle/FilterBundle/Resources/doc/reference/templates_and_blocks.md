Templates and Blocks
--------------------

Filter bundles provides several templates which can be used to customize filter view and functionality.

###Table of contents

- [Layout Template](#layout-template)
- [Header Template](#header-template)


###Layout Template

Layout template stores blocks which contain filter Javascript code to create object of appropriate filter.
This template is used by twig extension oro_filter_render_filter_javascript to render filter code.

Following table displays filter JavaScript class, parameters and block where they are stored.

<table>
<tr>
    <th>Filter</th>
    <th>Block Name</th>
    <th>JavaScript Class</th>
</tr>
<tr>
    <td>Text Filter</td>
    <td>oro_type_text_filter_js</td>
    <td>Oro.Filter.ChoiceFilter</td>
</tr>
<tr>
    <td>Number Filter</td>
    <td>oro_type_number_filter_js</td>
    <td>Oro.Filter.NumberFilter</td>
</tr>

<tr>
    <td>Date Range Filter</td>
    <td>oro_type_date_range_filter_js</td>
    <td>Oro.Filter.DateFilter</td>
</tr>

<tr>
    <td>DateTime Range Filter</td>
    <td>oro_type_datetime_range_filter_js</td>
    <td>Oro.Filter.DateTimeFilter</td>
</tr>

<tr>
    <td>Select Filter</td>
    <td>oro_type_select_filter_js</td>
    <td>Oro.Filter.SelectFilter</td>
</tr>

<tr>
    <td>Multiselect Filter</td>
    <td>oro_type_multiselect_filter_js</td>
    <td>Oro.Filter.MultiSelectFilter</td>
</tr>
</table>

Default template is stored in **OroFilterBundle:Filter:layout.js.twig**. Path to template is stored
in configuration in node **oro_filter > twig > layout**.

If developer wants to customize this template, he can extend default template, do required modification and
set modified template as used using configuration. The second possibility to customize this template name
is to redefine container parameter **oro_filter.twig.layout** and set desired template name.

```
oro_filter:
    twig:
        layout: AcmeMyBundle:Filter:layout.js.twig

# OR

parameters:
    oro_filter.twig.layout: AcmeMyBundle:Filter:layout.js.twig
```


###Header Template

Header template contains blocks with all files required for filters - it is *.js and *.css files.
By default this blocks just include external templates.

<table>
<tr>
    <th>Type</th>
    <th>Block Name</th>
    <th>Including Template</th>
</tr>
<tr>
    <td>JavaScript Files</td>
    <td>oro_filter_header_javascript</td>
    <td>OroFilterBundle:Header:javascript.html.twig</td>
</tr>
<tr>
    <td>Style Files</td>
    <td>oro_filter_header_stylesheet</td>
    <td>OroFilterBundle:Header:stylesheet.html.twig</td>
</tr>
</table>

Default template is stored in **OroFilterBundle:Filter:header.twig**.
Path to template is stored in configuration in node **oro_filter > twig > header**.

If developer wants to customize this template, he can extend default template, do required modification
and set modified template as used using configuration. The second possibility to customize this template name
is to redefine container parameter **oro_filter.twig.header** and set desired template name.

```
oro_filter:
    twig:
        header: AcmeMyBundle:Filter:header.html.twig

# OR

parameters:
    oro_filter.twig.header: AcmeMyBundle:Filter:header.html.twig
```
