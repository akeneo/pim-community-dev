<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Helper;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use PHPUnit\Framework\Assert;

trait EntityBuilderTrait
{
    /**
     * @return mixed
     */
    abstract protected function get(string $service);

    private function createChannel(array $channelData): void
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update(
            $channel,
            [
                'code' => $channelData['code'],
                'locales' => $channelData['locales'],
                'currencies' => $channelData['currencies'],
                'category_tree' => 'master'
            ]
        );

        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    protected function createAttribute(array $data): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function createAttributeOption(array $data): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    protected function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);

        $constraints = $this->get('validator')->validate($family);
        Assert::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    protected function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        $constraints = $this->get('validator')->validate($familyVariant);
        self::assertCount(0, $constraints, (string)$constraints);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    protected function createProduct(string $identifier, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            $data
        );

        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is not valid: %s', $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function createReferenceEntity(string $referenceEntityIdentifier): void
    {
        $createCommand = new CreateReferenceEntityCommand($referenceEntityIdentifier, []);
        $violations = $this->get('validator')->validate($createCommand);
        Assert::assertCount(0, $violations, (string) $violations);
        $handler = $this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler');
        $handler($createCommand);
    }

    protected function createRecord(string $referenceEntityIdentifier, string $code, array $labels): void
    {
        $createCommand = new CreateRecordCommand($referenceEntityIdentifier, $code, $labels);

        $violations = $this->get('validator')->validate($createCommand);
        self::assertCount(0, $violations, (string)$violations);

        $handler = $this->get('akeneo_referenceentity.application.record.create_record_handler');
        ($handler)($createCommand);
    }

    protected function deleteRecord(string $referenceEntityIdentifier, string $code): void
    {
        $deleteCommand = new DeleteRecordCommand($code, $referenceEntityIdentifier);

        $violations = $this->get('validator')->validate($deleteCommand);
        self::assertCount(0, $violations, (string)$violations);

        $handler = $this->get('akeneo_referenceentity.application.record.delete_record_handler');
        ($handler)($deleteCommand);
    }

    protected function deleteRecords(string $referenceEntityIdentifier, array $codes): void
    {
        $deleteCommand = new DeleteRecordsCommand($referenceEntityIdentifier, $codes);

        $violations = $this->get('validator')->validate($deleteCommand);
        self::assertCount(0, $violations, (string)$violations);

        $handler = $this->get('akeneo_referenceentity.application.record.delete_records_handler');
        ($handler)($deleteCommand);
    }
}
