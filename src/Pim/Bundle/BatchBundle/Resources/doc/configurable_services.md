Editable service (connector / job) configuration
------------------------------------------------

Make a service configuration editable means provide a way to get a form to create / edit the expected service configuration.

Define the configuration form
-----------------------------

Create a form type in classic SF 2 way (it uses the default validator).

Here we just extending AbstractConfigurationType to ensure presence of basic fields as id :
```php
<?php

namespace Acme\Bundle\DemoDataFlowBundle\Form\Type;

use Oro\Bundle\DataFlowBundle\Form\Type\AbstractConfigurationType;

class CsvConnectorType extends AbstractConfigurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('charset', 'text', array('required' => true));
        $builder->add('delimiter', 'text', array('required' => true));
        $builder->add('enclosure', 'text', array('required' => true));
        $builder->add('escape', 'text', array('required' => true));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array('data_class' => 'Acme\Bundle\DemoDataFlowBundle\Configuration\CsvConfiguration')
        );
    }

    public function getName()
    {
        return 'configuration_csv';
    }
}
```

Define form and form type as service :
```yaml
parameters:
    configuration.type.csv.class:              Acme\Bundle\DemoDataFlowBundle\Form\Type\CsvConnectorType

services:
    configuration.form.type.csv:
        class: %configuration.type.csv.class%
        tags:
            - { name: form.type, alias: configuration_csv}
```


Make the configuration editable
-------------------------------

Define the required method in configuration to provide form type service alias.

Then in default views this configuration can be edited with the related form type.

```php
<?php

namespace Acme\Bundle\DemoDataFlowBundle\Configuration;

use Oro\Bundle\DataFlowBundle\Configuration\AbstractConfiguration;
use JMS\Serializer\Annotation\Type;

class CsvConfiguration extends AbstractConfiguration
{
    /**
     * {@inheritDoc}
     */
    public function getFormTypeServiceId()
    {
        return "configuration_csv";
    }
}
```
