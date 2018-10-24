<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Subscription;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationFake;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * It is possible to remove the family from a product.
 * In this case, products affected need to be unsubscribed from Franklin, as only
 * products with a family can be subscribed.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class UnsubscribeProductsAfterFamilyRemovalIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->activateFranklinInsights();
        $this->loadFamilyAndAttributes('router');
        $this->loadProductOfFamily('B00EYZY6AC', 'router');
        $this->createIdentifierMapping(['asin' => 'sku']);
    }

    public function test_that_a_product_is_unsubscribed_from_franklin_when_its_family_is_removed(): void
    {
        $this->givenTheProductIsSubscribedToFranklin('B00EYZY6AC');
        $this->whenTheFamilyIsRemovedFromProduct('B00EYZY6AC');
        $this->thenTheProductsShouldNotBeSubscribedAnymore(['B00EYZY6AC']);
    }

    public function test_that_several_products_are_unsubscribed_from_franklin_when_their_family_are_removed(): void
    {
        $this->givenSeveralProductsAreSubscribedToFranklin(['B00EYZY6AC']);
        $this->whenTheFamilyIsRemovedFromSeveralProductsAtOnce(['B00EYZY6AC']);
        $this->thenTheProductsShouldNotBeSubscribedAnymore(['B00EYZY6AC']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * Subscribes a product to Franklin.
     *
     * @param string $productIdentifier
     */
    private function givenTheProductIsSubscribedToFranklin(string $productIdentifier): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);

        $command = new SubscribeProductCommand($product->getId());
        $this->get('akeneo.pim.automation.suggest_data.handler.subscribe_product')->handle($command);

        $subscription = $this
            ->get('akeneo.pim.automation.suggest_data.repository.product_subscription')
            ->findOneByProductId($product->getId());

        Assert::isInstanceOf($subscription, ProductSubscription::class);
    }

    /**
     * Subscribes several products to Franklin.
     *
     * @param string[] $productIdentifiers
     */
    private function givenSeveralProductsAreSubscribedToFranklin(array $productIdentifiers): void
    {
        foreach ($productIdentifiers as $productIdentifier) {
            $this->givenTheProductIsSubscribedToFranklin($productIdentifier);
        }
    }

    /**
     * Sets the family of a product to "null".
     *
     * @param string $productIdentifier
     */
    private function whenTheFamilyIsRemovedFromProduct(string $productIdentifier): void
    {
        $product = $this->removeFamilyFromProduct($productIdentifier);

        $this->get('pim_catalog.saver.product')->save($product);
    }

    /**
     * @param string[] $productIdentifiers
     */
    private function whenTheFamilyIsRemovedFromSeveralProductsAtOnce(array $productIdentifiers): void
    {
        $products = [];
        foreach ($productIdentifiers as $productIdentifier) {
            $products[] = $this->removeFamilyFromProduct($productIdentifier);
        }

        $this->get('pim_catalog.saver.product')->saveAll($products);
    }

    /**
     * @param string $productIdentifier
     *
     * @return ProductInterface
     */
    private function removeFamilyFromProduct(string $productIdentifier): ProductInterface
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);

        $this->get('pim_catalog.updater.product')->update($product, ['family' => null]);

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Product "%s" is not valid, cf following constraint violations "%s"',
                    $product->getIdentifier(),
                    implode(', ', $messages)
                )
            );
        }

        return $product;
    }

    /**
     * Checks that a product is not subscribed anymore.
     *
     * @param string[] $productIdentifiers
     */
    private function thenTheProductsShouldNotBeSubscribedAnymore(array $productIdentifiers): void
    {
        foreach ($productIdentifiers as $productIdentifier) {
            $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);

            $subscription = $this
                ->get('akeneo.pim.automation.suggest_data.repository.product_subscription')
                ->findOneByProductId($product->getId());

            Assert::null($subscription);
        }
    }

    /**
     * @param array $mapping
     */
    private function createIdentifierMapping(array $mapping): void
    {
        $command = new UpdateIdentifiersMappingCommand(array_merge(
            array_fill_keys(IdentifiersMapping::PIM_AI_IDENTIFIERS, null),
            $mapping
        ));
        $this
            ->get('akeneo.pim.automation.suggest_data.handler.update_identifiers_mapping')
            ->handle($command);
    }

    /**
     * Activates the connection to a fake Franklin.
     */
    private function activateFranklinInsights(): void
    {
        $command = new ActivateConnectionCommand(new Token(AuthenticationFake::VALID_TOKEN));
        $this
            ->get('akeneo.pim.automation.suggest_data.application.configuration.command.activate_connection_handler')
            ->handle($command);
    }

    /**
     * Loads a product in database.
     *
     * @param string $productIdentifier
     * @param string $familyCode
     */
    private function loadProductOfFamily(string $productIdentifier, string $familyCode): void
    {
        $normalizedProduct = $this->loadJsonFileAsArray(sprintf(
            'products/product-%s-%s.json',
            $familyCode,
            $productIdentifier
        ));

        $productBuilder = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder');

        $productBuilder
            ->withIdentifier($productIdentifier)
            ->withFamily($familyCode);

        foreach ($normalizedProduct['values'] as $attrCode => $value) {
            $productBuilder->withValue($attrCode, $value[0]['data']);
        }
        $product = $productBuilder->build();

        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);
    }

    /**
     * Loads an family in database, with all its attributes.
     *
     * @param string $familyCode
     */
    private function loadFamilyAndAttributes(string $familyCode): void
    {
        $normalizedFamily = $this->loadJsonFileAsArray(sprintf(
            'families/family-%s.json',
            $familyCode
        ));

        $this->loadAttributes($normalizedFamily['attributes']);

        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build($normalizedFamily);
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);
    }

    /**
     * Loads attributes in database, according to a provided list of attribute codes.
     *
     * @param array $attributeCodes
     */
    private function loadAttributes(array $attributeCodes): void
    {
        $normalizedAttributes = $this->loadJsonFileAsArray('attributes/attributes.json');

        $attributes = [];
        foreach ($attributeCodes as $attributeCode) {
            if ('sku' === $attributeCode) {
                continue;
            }
            $attributes[] = $this
                ->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')
                ->build($normalizedAttributes[$attributeCode]);
        }

        $this->getFromTestContainer('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    /**
     * Loads a file containing JSON content and return it as a PHP array.
     *
     * @param string $filepath
     *
     * @return array
     */
    private function loadJsonFileAsArray(string $filepath): array
    {
        $filepath = realpath(sprintf(
            '%s/src/Akeneo/Pim/Automation/SuggestData/tests/back/Acceptance/Resources/fixtures/%s',
            $this->getParameter('kernel.project_dir'),
            $filepath
        ));
        Assert::true(file_exists($filepath));
        $jsonContent = file_get_contents($filepath);

        return json_decode($jsonContent, true);
    }
}
