<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

abstract class AbstractQuantifiedAssociationIntegration extends TestCase
{
    protected function givenBooleanAttributes(array $codes): void
    {
        $attributes = array_map(function (string $code) {
            $data = [
                'code' => $code,
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
            ];
            $attribute = $this->getAttributeFactory()->create();
            $this->getAttributeUpdater()->update($attribute, $data);
            $constraints = $this->getValidator()->validate($attribute);

            Assert::count($constraints, 0);

            return $attribute;
        }, $codes);
        $this->getAttributeSaver()->saveAll($attributes);
    }

    protected function givenFamily(array $normalizedFamily): void
    {
        $family = $this->getFamilyFactory()->create();
        $this->getFamilyUpdater()->update($family, [
            'code' => $normalizedFamily['code'],
            'attributes'  => array_merge(['sku'], $normalizedFamily['attribute_codes']),
            'attribute_requirements' => ['ecommerce' => ['sku']]
        ]);

        $errors = $this->getValidator()->validate($family);
        Assert::count($errors, 0);

        $this->getFamilySaver()->save($family);
    }

    protected function getEntityBuilder(): EntityBuilder
    {
        return $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
    }

    protected function getAssociationTypeRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.association_type');
    }

    protected function getProductRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product');
    }

    protected function getProductModelRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product_model');
    }

    protected function getAssociationTypeRepository(): AssociationTypeRepositoryInterface
    {
        return $this->get('pim_catalog.repository.association_type');
    }

    private function getValidator(): ValidatorInterface
    {
        return $this->get('validator');
    }

    private function getAttributeFactory(): SimpleFactoryInterface
    {
        return $this->get('pim_catalog.factory.attribute');
    }

    private function getAttributeUpdater(): ObjectUpdaterInterface
    {
        return $this->get('pim_catalog.updater.attribute');
    }

    private function getAttributeSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.attribute');
    }

    private function getFamilyFactory(): SimpleFactoryInterface
    {
        return $this->get('pim_catalog.factory.family');
    }

    private function getFamilyUpdater(): ObjectUpdaterInterface
    {
        return $this->get('pim_catalog.updater.family');
    }

    private function getFamilySaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.family');
    }
}
