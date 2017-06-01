<?php

namespace Pim\Component\Catalog\tests\integration\Updater;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\AssociationTypeInterface;

class AssociationTypeUpdaterIntegration extends TestCase
{
    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidObjectException
     * @expectedExceptionMessage Expects a "Pim\Component\Catalog\Model\AssociationTypeInterface", "stdClass" given.
     */
    public function testUpdateObjectInAssociationTypeUpdater()
    {
        $this->getUpdater()->update(new \stdClass(), []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "code" expects a scalar as data, "array" given.
     */
    public function testAssociationTypeUpdateWithNonScalarCode()
    {
        $associationType = $this->createAssociationType();

        $this->getUpdater()->update($associationType, ['code' => []]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "labels" expects an array as data, "NULL" given.
     */
    public function testAssociationTypeUpdateWithNullLabels()
    {
        $associationType = $this->createAssociationType();

        $this->getUpdater()->update($associationType, ['labels' => null]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage one of the "labels" values is not a scalar
     */
    public function testAssociationTypeUpdateWithNonScalarLabels()
    {
        $associationType = $this->createAssociationType();

        $this->getUpdater()->update($associationType, ['labels' => ['en_US' => []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\UnknownPropertyException
     * @expectedExceptionMessage Property "unknown_property" does not exist.
     */
    public function testAssociationTypeUpdateWithUnknownProperty()
    {
        $associationType = $this->createAssociationType();

        $this->getUpdater()->update($associationType, ['unknown_property' => null]);
    }

    public function testSuccessAssociationTypeUpdate()
    {
        $associationType = $this->createAssociationType();
        $data = [
            'code'   => 'NEW_SELL',
            'labels' => [
                'en_US' => 'New sell',
                'fr_FR' => 'Nouvelle vente',
            ],
        ];

        $this->getUpdater()->update(
            $associationType,
            $data
        );

        $this->assertSame($data['code'], $associationType->getCode());
        $this->assertSame($data['labels']['fr_FR'], $associationType->getTranslation('fr_FR')->getLabel());
        $this->assertSame($data['labels']['en_US'], $associationType->getTranslation('en_US')->getLabel());
    }

    /**
     * @return AssociationTypeInterface
     */
    protected function createAssociationType()
    {
        return $this->get('pim_catalog.factory.association_type')->create();
    }

    /**
     * @return AssociationTypeInterface
     */
    protected function getUpdater()
    {
        return $this->get('pim_catalog.updater.association_type');
    }

    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()]
        );
    }
}
