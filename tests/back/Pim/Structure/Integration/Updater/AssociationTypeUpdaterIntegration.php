<?php

namespace AkeneoTest\Pim\Structure\Integration\Updater;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;

class AssociationTypeUpdaterIntegration extends TestCase
{
    public function testUpdateObjectInAssociationTypeUpdater()
    {
        $this->expectException(InvalidObjectException::class);
        $this->expectExceptionMessage('Expects a "Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface", "stdClass" given.');

        $this->getUpdater()->update(new \stdClass(), []);
    }

    public function testAssociationTypeUpdateWithNonScalarCode()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "code" expects a scalar as data, "array" given.');

        $associationType = $this->createAssociationType();

        $this->getUpdater()->update($associationType, ['code' => []]);
    }

    public function testAssociationTypeUpdateWithNullLabels()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "labels" expects an array as data, "NULL" given.');

        $associationType = $this->createAssociationType();

        $this->getUpdater()->update($associationType, ['labels' => null]);
    }

    public function testAssociationTypeUpdateWithNonScalarLabels()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('one of the "labels" values is not a scalar');

        $associationType = $this->createAssociationType();

        $this->getUpdater()->update($associationType, ['labels' => ['en_US' => []]]);
    }

    public function testAssociationTypeUpdateWithUnknownProperty()
    {
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Property "unknown_property" does not exist.');

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
        return $this->catalog->useTechnicalCatalog();
    }
}
