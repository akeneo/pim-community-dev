<?php

declare(strict_types=1);

namespace Context;

use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\Assert;
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
        // Create family
        $family = $this->getService('pim_catalog.factory.family')->create();
        $updater = $this->getService('pim_catalog.updater.family');
        $updater->update($family, [
            'code' => 'chair',
            'attributes' => ['sku', 'brand'],
        ]);

        $errors = $this->getService('validator')->validate($family);
        Assert::assertCount(0, $errors);

        $this->getService('pim_catalog.saver.family')->save($family);

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

    /**
     * @Given a record job import in CSV
     */
    public function aRecordJobImportInCSV(): void
    {
        $jobInstance = new JobInstance('Akeneo CSV Connector', 'import', 'csv_reference_entity_record_import');
        $jobInstance->setCode('test_csv');
        $jobInstance->setLabel('Record CSV import');
        $this->getService('akeneo_batch.saver.job_instance')->save($jobInstance);
    }

    /**
     * @Given a record job import in XLSX
     */
    public function aRecordJobImportInXLSX(): void
    {
        $jobInstance = new JobInstance('Akeneo CSV Connector', 'import', 'xlsx_reference_entity_record_import');
        $jobInstance->setCode('test_xlsx');
        $jobInstance->setLabel('Record XLSX import');
        $this->getService('akeneo_batch.saver.job_instance')->save($jobInstance);
    }
}
