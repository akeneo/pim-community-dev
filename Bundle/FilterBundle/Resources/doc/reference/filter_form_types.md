Filters Form Types
------------------

### Table of Contents

- [Filter Form Types](#filter-form-types)
- [Example of Usage](#example-of-usage)
- [References](#references)

### Overview

PHP classes that represent filters and extend standard Symfony form types. 
Each filter form types is compound and consists of two fields:

* field for filter value (named as "value")
* field for filter operator (named as "type")

There are next filters form types:

<table>
<tr>
    <th>Class</th>
    <th>Name</th>
    <th>Short Description</th>
</tr>
<tr>
    <td><a href="#oro_type_filter-form-type">FilterType</a></td>
    <td>oro_type_filter</td>
    <td>Basic type for all filters, declares two children value and type.</td>
</tr>
<tr>
    <td><a href="#oro_type_text_filter-form-type">TextFilterType</a></td>
    <td>oro_type_text_filter</td>
    <td>Represents text filter form</td>
</tr>
<tr>
    <td><a href="#oro_type_number_filter-form-type">NumberFilterType</a></td>
    <td>oro_type_number_filter</td>
    <td>Represents number filter form</td>
</tr>
<tr>
    <td><a href="#oro_type_choice_filter-form-type">ChoiceFilterType</a></td>
    <td>oro_type_choice_filter</td>
    <td>Represents choice filter form</td>
</tr>
<tr>
    <td><a href="#oro_type_entity_filter-form-type">EntityFilterType</a></td>
    <td>oro_type_entity_filter</td>
    <td>Represents entity filter form</td>
</tr>
<tr>
    <td><a href="#oro_type_boolean_filter-form-type">BooleanFilterType</a></td>
    <td>oro_type_boolean_filter</td>
    <td>Represents boolean filter form</td>
</tr>
<tr>
    <td><a href="#oro_type_date_range_filter-form-type">DateRangeFilterType</a></td>
    <td>oro_type_date_range_filter</td>
    <td>Represents date filter form</td>
</tr>
<tr>
    <td><a href="#oro_type_datetime_range_filter-form-type">DateTimeRangeFilterType</a></td>
    <td>oro_type_datetime_range_filter</td>
    <td>Represents date and time filter form</td>
</tr>
<tr>
    <td><a href="#oro_type_date_range-form-type">DateRangeType</a></td>
    <td>oro_type_date_range</td>
    <td>This form type is used by oro_type_date_range_filter as field type</td>
</tr>
<tr>
    <td><a href="#oro_type_datetime_range-form-type">DateTimeRangeType</a></td>
    <td>oro_type_datetime_range</td>
    <td>This form type is used by oro_type_datetime_range_filter as field type</td>
</tr>
<tr>
    <td><a href="#oro_type_selectrow">SelectRowFilterType</a></td>
    <td>oro_type_selectrow_filter</td>
    <td>This form type is used by datagrid extension only</td>
</tr>
</table>

### oro\_type\_filter Form Type

**Children**

* value
* type

**Options**

* field_type
* field_options
* operator_choices
* operator_type
* operator_options
* show_filter

**Default Options**

* field_type = "text"
* operator_type = "choice"
* show_filter = False

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType_

**Options Description**

* **field_type** - This option declares type of value child element.
* **field_options** - Value of this option will be used as options array for value field.
* **operator_choices** - Value of this option will be used as value of "choices" option of type field.
* **operator_type** - This option declares type of type child element. By default has "choice" value.
* **operator_options** - Value of this option will be used as options array for type field.
* **show_filter** - If FALSE then filter will be hidden when it's rendered in filter list.

### oro\_type\_text\_filter Form Type

**Inherit Options**

* field_type
* field_options
* operator_choices
* operator_type
* operator_options
* show_filter

**Default Options**

* field_type = text
* operator\_choices
    * TextFilterType::TYPE\_CONTAINS
    * TextFilterType::TYPE\_NOT_CONTAINS
    * TextFilterType::TYPE\_EQUAL

**Parent Type**

oro\_type\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType_

**JS Class**

_Oro.Filter.TextFilter_


### oro\_type\_number\_filter Form Type

**Options**

* data_type
* fromatter_options

**Inherit Options**

* field_type
* field_options
* operator_choices
* operator_type
* operator_options
* show_filter

**Default Options**

* field_type = text
* operator\_choices
    * NumberFilterType::TYPE\_GREATER\_EQUAL
    * NumberFilterType::TYPE\_GREATER\_THAN
    * NumberFilterType::TYPE\_EQUAL
    * NumberFilterType::TYPE\_LESS\_EQUAL
    * NumberFilterType::TYPE\_LESS\_THAN
* data\_type = NumberFilterType::DATA\_INTEGER

**Parent Type**

oro\_type\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType_

**JS Class**

_Oro.Filter.NumberFilter_

**Options**

* **data\_type** - This option can be used for configuration of value field type. Can be a value of one of constants: 
NumberFilterType::DATA\_INTEGER or NumberFilterType::DATA\_DECIMAL.

**formatter_options**

In addition to data_type option, this option can contain parameters for number formatter that is used by value field. 
Available attributes are:

* decimals - maximum fraction digits
* grouping - use grouping to separate digits
* orderSeparator - symbol of grouping separator
* decimalSeparator - symbol of decimal separator.

### oro\_type\_choice\_filter Form Type

**Inherit Options**

* field\_type
* field\_options
* operator\_choices
* operator\_type
* operator\_options
* show\_filter

**Default Options**

* field\_type = choice
* operator\_choices
    * ChoiceFilterType::TYPE\_CONTAINS
    * ChoiceFilterType::TYPE\_NOT\_CONTAINS

**Parent Type**

oro\_type\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType_

**JS Classes**

_Oro.Filter.MultiSelectFilter_
_Oro.Filter.SelectFilter_

### oro\_type\_entity\_filter Form Type

**Inherit Options**

* field\_type
* field\_options
* operator\_choices
* operator\_type
* operator\_options
* show\_filter

**Default Options**

* field\_type = entity

**Parent Type**

oro\_type\_choice\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType_

**JS Classes**

_Oro.Filter.MultiSelectFilter_
_Oro.Filter.SelectFilter_

### oro\_type\_boolean\_filter Form Type

**Inherit Options**

* field\_type
* field\_options
* operator\_choices
* operator\_type
* operator\_options
* show\_filter

**Default Options**

* field\_options = choices
    * BooleanFilterType::TYPE\_YES
    * BooleanFilterType::TYPE\_NO

**Parent Type**

oro\_type\_choice\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType_

**JS Class**

_Oro.Filter.SelectFilter_

### oro\_type\_date\_range\_filter Form Type

**Options**

* widget\_options
* type\_values

**Inherit Options**

* field\_type
* field\_options
* operator\_choices
* operator\_type
* operator\_options
* show\_filter

**Default Options**

* field\_type = oro\_type\_date\_range
* widget\_options = array("dateFormat" => "mm/dd/yy", "firstDay" => 0)
* operator\_choices
    * DateRangeFilterType::TYPE\_BETWEEN
    * DateRangeFilterType::TYPE\_NOT\_BETWEEN
* type\_values
    * DateRangeFilterType::TYPE\_BETWEEN
    * DateRangeFilterType::TYPE\_NOT\_BETWEEN

**Parent Type**

oro\_type\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType_

**JS Classes**

_Oro.Filter.DateFilter_

**Options Description**

* **widget\_options** - Value of this option will be used by javascript widget to correctly display it's data. 
Default value of this option depend from of current application locale options.
* **type\_values** - Value of this option will be used by javascript widget to generate valid hint of 
current filter value (strings like "between %start% and %end%", "before %start%", "after %end%", 
"not between %start%", etc)

### oro\_type\_datetime\_range\_filter Form Type

**Options**

* widget\_options
* type\_values

**Inherit Options**

* field\_type
* field\_options
* operator\_choices
* operator\_type
* operator\_options
* show\_filter
* type\_values
* widget\_options

**Default Options**

* field\_type = oro\_type\_datetime\_range
* widget\_options = array("dateFormat" => "mm/dd/yy", "timeFormat" => "hh:mm tt", "firstDay" => 0)

**Parent Type**

oro\_type\_date\_range\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType_

**JS Classes**

_Oro.Filter.DateFilter_

### oro\_type\_date\_range Form Type

**Children**

* start
* end

**Options**

* field\_type
* field\_options
* start\_field\_options
* end\_field\_options

**Default Options**

* field\_type = "date"

**Class**

_Oro\Bundle\FilterBundle\Form\Type\DateRangeType_

**Options Description**

* **field\_type** - This option declares type of start and end child elements.
* **field\_options** - Value of this option will be used as options array for start and end fields.
* **start\_field\_options** - Value of this option will be used as options array for start field.
* **end\_field\_options** - Value of this option will be used as options array for end field.

### oro\_type\_datetime\_range Form Type

**Default Options**

* field\_type = "datetime"

**Parent Type**

oro\_type\_date\_range

**Class**

_Oro\Bundle\FilterBundle\Form\Type\DateTimeRangeType_

### oro\_type\_selectrow  Form Type

Provides filtering by selected/not selected rows in datagrid

**Default Options**

* field\_type = "choice"

**Parent Type**

oro\_type\_filter

**Class**

_Oro\Bundle\FilterBundle\Form\Type\SelectRowFilterType_


Example of Usage
----------------

You can use filter form types as any other form type in Symfony. For example, consider you have a form 
with three filters. In your form type you should add code like this:

``` php
class MyFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Add some form fields
        // ...
        // Add filters
        $builder->add('name', 'oro_type_text_filter');
        $builder->add('salary', 'oro_type_number_filter');
        $builder->add('hobby', 'oro_type_choice_filter', array(
        field_options' => array(
                'choices' => array(1 => 'Coding', 2 => 'Hiking', 3 => 'Photography'),
                'multiple' => true
            )
        ));
    }
}
```

References
----------

* Symfony 2 Form Types
    * http://symfony.com/doc/master/cookbook/form/create_custom_field_type.html
    * http://symfony.com/doc/master/reference/forms/types.html
