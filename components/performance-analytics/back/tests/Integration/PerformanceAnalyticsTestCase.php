<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\PerformanceAnalytics\Integration;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class PerformanceAnalyticsTestCase extends TestCase
{
    protected MessageBusInterface $messageBus;
    protected ProductRepositoryInterface $productRepository;

    /**
     * {@inheritDoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function createCategory(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $violations = $this->get('validator')->validate($category);
        self::assertEmpty($violations, (string) $violations);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents): ProductInterface
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->messageBus->dispatch($command);

        return $this->productRepository->findOneByIdentifier($identifier);
    }

    protected function createProductModel(string $code, ?string $parentCode, string $familyVariant, array $categoryCodes): void
    {
        $productModelBuilder = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->withCategories(...$categoryCodes)
        ;
        if (null !== $parentCode) {
            $productModelBuilder->withParent($parentCode);
        }

        $productModel = $productModelBuilder->build();
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    protected function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        self::assertNotNull($user);

        return $user->getId();
    }

    protected function createFamily(string $code, array $data = []): FamilyInterface
    {
        $data = array_merge(['code' => $code], $data);

        $data['attributes'] = \array_unique(\array_merge(['sku'], $data['attributes']));
        $family = $this->get('akeneo_integration_tests.base.family.builder')->build($data);

        $violations = $this->get('validator')->validate($family);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createFamilyVariant(string $code, string $family, array $data = []): FamilyVariantInterface
    {
        $data = array_merge(['code' => $code, 'family' => $family], $data);

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);

        $violations = $this->get('validator')->validate($familyVariant);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    protected function createAttribute(string $code, array $data = []): AttributeInterface
    {
        $defaultData = [
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ];
        $data = array_merge($defaultData, $data);

        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build($data, true);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function createAttributeOptions(string $attributeCode, array $optionsCodes): void
    {
        $attributeOptions = [];
        foreach ($optionsCodes as $optionCode) {
            $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                'code' => $optionCode,
                'attribute' => $attributeCode,
            ]);
            $attributeOptions[] = $attributeOption;
        }

        $this->get('pim_catalog.saver.attribute_option')->saveAll($attributeOptions);
    }

    protected function createChannel(array $data = []): ChannelInterface
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $data);

        $violations = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $violations, $violations->__toString());

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }
}
