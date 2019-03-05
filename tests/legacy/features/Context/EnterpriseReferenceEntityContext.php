<?php

declare(strict_types=1);

namespace Context;

use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Pim\Behat\Context\PimContext;

class EnterpriseReferenceEntityContext extends PimContext
{
    /**
     * @Given /^a reference entity simple link attribute$/
     */
    public function aReferenceEntitySimpleLinkAttribute()
    {
        // Create the Reference entity
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [],
            Image::createEmpty()
        );
        $this->getService('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')
            ->create($referenceEntity);

        // Create a record for this reference entity
        $record = Record::create(
            RecordIdentifier::fromString('ikea'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('ikea'),
            ValueCollection::fromValues([])
        );
        $this->getService('akeneo_referenceentity.infrastructure.persistence.repository.record')
            ->create($record);

        $record = Record::create(
            RecordIdentifier::fromString('sony'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('sony'),
            ValueCollection::fromValues([])
        );
        $this->getService('akeneo_referenceentity.infrastructure.persistence.repository.record')
            ->create($record);


        // Create a new attribute linked to this reference entity
        $attribute = $this->getService('pim_catalog.factory.attribute')
            ->createAttribute('akeneo_reference_entity');
        $attribute->setCode('brand');
        $group = $this->getService('pim_catalog.repository.attribute_group')->findOneByIdentifier('other');
        $attribute->setGroup($group);
        $attribute->setUseableAsGridFilter(true);
        $attribute->setReferenceDataName('brand');
        $this->getService('pim_catalog.saver.attribute')
            ->save($attribute);
    }

    /**
     * @Given /^a product with a value for this reference entity simple link attribute$/
     */
    public function aProductWithAValueForThisReferenceEntitySimpleLinkAttribute()
    {
        $attribute = $this->getService('pim_catalog.repository.attribute')
            ->findOneByIdentifier('brand');

        // Create family
        $family = new Family();
        $family->setCode('chair');
        $family->setLocale('en_US')->setLabel('Chair');
        $family->addAttribute($attribute);

        $this->getService('pim_catalog.saver.family')
            ->save($family);

        // Create product
        $product = $this->getService('pim_catalog.builder.product')
            ->createProduct('tabouret', 'chair');

        $this->getService('pim_catalog.updater.product')
            ->update($product, [
                'values' => [
                    'brand' => [['locale' => null, 'scope' => null, 'data' => 'ikea']]
                ]
            ]);

        $this->getService('pim_catalog.saver.product')
            ->save($product);
    }
}
