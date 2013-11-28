Properties
----------

Property is an entity that responsible for providing values for grid results. It can for example be a value for some column. When grid results are converting to some format (e.g. json) all grid properties will be asked to provide a values for each result element. Property Collection aggregates list of Properties.

#### Class Description

* **Property \ PropertyInterface** - basic interface for Property, provides specific value of result data element;
* **Property \ AbstractProperty** - abstract class for Property, knows how to get values from arrays and objects using the most appropriate way - public methods "get<Name>" or "is<Name>" or public property;
* **Property \ FieldProperty** - by default Field Description has this type of property, it knows how to get right value from data based on field name and field type;
* **Property \ FixedProperty** - simple property implementation that extracts data by fixed value key;
* **Property \ UrlProperty** - can generate URL as it's value using Router, route name and the list of data property names that should be used as route parameters;
* **Property \ TwigPropertyInterface** - interface that allows properties to work with twig environment;
* **Property \ TwigTemplateProperty** - generates value based on twig template;
* **Property \ PropertyCollection** - collection of properties.

#### Example of Getting Values

``` php
$data = array();
/** @var $datagridObject \Oro\Bundle\GridBundle\Datagrid\Datagrid */
foreach ($datagridObject->getResults() as $object) {
    $record = array();
    foreach ($datagridObject->getProperties() as $property) {
        if ($property instanceof \Oro\Bundle\GridBundle\Property\TwigPropertyInterface) {
            $property->setEnvironment($twigEnvironment);
        }
        $record[$property->getName()] = $property->getValue($object);
    }
    $data[] = $record;
}
```

#### Example of Creating Properties

``` php
class UserDatagridManager extends DatagridManager
{
    protected function getProperties()
    {
        return array(
            new UrlProperty('show_link', $this->router, 'oro_user_view', array('id')),
            new UrlProperty('edit_link', $this->router, 'oro_user_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_user', array('id')),
        );
    }
    // ... other methods
}
```
