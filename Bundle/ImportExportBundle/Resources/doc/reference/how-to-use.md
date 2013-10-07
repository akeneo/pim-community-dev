How To Use
==========

Table of Contents
-----------------
 - [Adding Normalizers](#adding-normalizers)
 - [Adding Data Converter](#adding-data-converter)
 - [Export Processor](#export-processor)
 - [Import Strategy](#import-strategy)
 - [Import Processor](#import-processor)

Adding Normalizers
------------------

Serializer is involved both in import and export operations. It's stands on standard Symfony's serializer and uses
it's interfaces. Responsiblity of serializer is converting entities to plain/array representation (serialization)
and vice versa converting plain/array representation to entity objects (deserialization).

Serializer uses normalizers to perform converting of objects. So you need to provide normalizers for entities that
will be imported/exported.

Requirement to normalizer is to implement interfaces:
* **Symfony\Component\Serializer\Normalizer\NormalizerInterface** - used in export
* **Symfony\Component\Serializer\Normalizer\DenormalizerInterface** - used in import

Generally you should implement both interfaces if you need to add both import and export for entity.

**Example of simple normalizer**

```
<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\ContactBundle\Entity\Group;

class GroupNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'id' => $object->getId(),
            'label' => $object->getLabel(),
        );
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = new Group();
        if (!empty($data['id'])) {
            $result->setId($data['id']);
        }
        if (!empty($data['label'])) {
            $result->setLabel($data['label']);
        }
        return $result;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Group;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == 'OroCRM\Bundle\ContactBundle\Entity\Group';
    }
}

```

Serializer of OroImportExportBundle should be aware of it's normalizer. To make it possible use appropriate tag in DI
configuration:

**Example of normalizer service configuration**

```
parameters:
    orocrm_contact.importexport.normalizer.group.class: OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\GroupNormalizer
services:
    orocrm_contact.importexport.normalizer.group:
        class: %orocrm_contact.importexport.normalizer.group.class%
        tags:
            - { name: oro_importexport.normalizer }
```


Adding Data Converter
---------------------

Data converter is responsible for converting header of import/export file. Assume that your entity has some properties
that should be exposed in export file. You can use default Data Converter
(Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter) but if there is a need to have custom labels instead of
names of properties in export/import files, you can extend Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter.

**Example Of Custom Data Converter**

```
<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;
use OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactHeaderProvider;

class GroupDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return array('ID' => 'id', 'Label' => 'label');
    }

    /**
     * {@inheritDoc}
     */
    protected function getBackendHeader()
    {
        return array('id', 'label');
    }
}

```

Look at more complex example of DataConverter in OroCRMContactBundle
(OroCRM\Bundle\ContactBundle\ImportExport\Converter\ContactDataConverter).


Export Processor
----------------

At this point after normalizers are registered and data converter is available export can be already configured using
DI configuration.

```
services:
    orocrm_contact.importexport.processor.export_group:
        parent: oro_importexport.processor.export_abstract
        calls:
             - [setDataConverter, [@orocrm_contact.importexport.data_converter.group]]
        tags:
            - { name: oro_importexport.processor, type: export, entity: %orocrm_contact.group.entity.class%, alias: orocrm_contact_group }
```

There is a controller in OroImportExportBundle that can be utilized to request export CSV file. See controller action
OroImportExportBundle:ImportExport:instantExport (route **oro_importexport_export_instant**).

Now if you'll send a request to URL **/export/instant/orocrm_contact_group** you will receive a response with URL
of result exported file and some additional information:

```
{
    "success":true,
    "url":"/export/download/orocrm_contact_group_2013_10_03_13_44_53_524d4aa53ffb9.csv",
    "readsCount":3,
    "errorsCount":0
}
```

Import Strategy
---------------

Strategy is class a that responsible for processing import logic. For example import could add new records or
it can update only existed ones.


**Example of Import Strategy**

```
<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import;

use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

class AddOrReplaceStrategy implements StrategyInterface
{
    public function process($entity)
    {
        $result = $this->findGroup($entity->getId());
        if (!$result) {
            $result = $this->createGroup();
        }
        $this->replaceGroupProperties($result, $entity);
        $this->validateGroup($result);
        return $result;
    }

    // other methods
```

Import Processor
----------------

At this point after normalizers are registered, data converter is available and strategy is implemented import can be
already configured using DI configuration.

```
services:
    # Import processor
    orocrm_contact.importexport.processor.import_group:
        parent: oro_importexport.processor.import_abstract
        calls:
             - [setDataConverter, [@orocrm_contact.importexport.data_converter.group]]
             - [setStrategy, [@orocrm_contact.importexport.strategy.import.group.add_or_replace]]
        tags:
            - { name: oro_importexport.processor, type: import, entity: %orocrm_contact.group.entity.class%, alias: orocrm_contact.add_or_replace_group }
            - { name: oro_importexport.processor, type: import_validation, entity: %orocrm_contact.entity.class%, alias: orocrm_contact.add_or_replace_group }
```

Note that for import should be a processor for import validation as in example above.

Import can be done in three steps.

At the first step user fill out the form with source file that he want to import and submit it. See controller action
OroImportExportBundle:ImportExport:importForm (route "oro_importexport_import_form"), this action require parameter
"entity" which is a class name of entity that will be imported.

At the second step import validation is triggered. See controller action OroImportExportBundle:ImportExport:importValidate
(route "oro_importexport_import_validate"). As a result a user will see all actions that will be performed by import and
errors that were occurred. Records with errors can't be imported but errors not blocks valid records.

At the last step import is processed. See controller action OroImportExportBundle:ImportExport:importProcess
(route "oro_importexport_import_process").
