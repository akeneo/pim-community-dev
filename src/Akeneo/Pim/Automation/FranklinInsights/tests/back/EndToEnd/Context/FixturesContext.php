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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\EndToEnd\Context;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\Gherkin\Node\TableNode;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FixturesContext extends PimContext
{
    /** @var SaveIdentifiersMappingHandler */
    private $saveIdentifiersMappingHandler;

    /** @var SubscribeProductHandler */
    private $subscribeProductHandler;

    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var BulkSaverInterface */
    private $attributeSaver;

    /** @var EntityBuilder */
    private $familyBuilder;

    /** @var SaverInterface */
    private $familySaver;

    /** @var Builder\Product */
    private $productBuilder;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param string $mainContextClass
     * @param SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler
     * @param SubscribeProductHandler $subscribeProductHandler
     * @param EntityBuilder $attributeBuilder
     * @param BulkSaverInterface $attributeSaver
     * @param EntityBuilder $familyBuilder
     * @param SaverInterface $familySaver
     * @param Builder\Product $productBuilder
     * @param SaverInterface $productSaver
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        string $mainContextClass,
        SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler,
        SubscribeProductHandler $subscribeProductHandler,
        EntityBuilder $attributeBuilder,
        BulkSaverInterface $attributeSaver,
        EntityBuilder $familyBuilder,
        SaverInterface $familySaver,
        Builder\Product $productBuilder,
        SaverInterface $productSaver,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($mainContextClass);

        $this->saveIdentifiersMappingHandler = $saveIdentifiersMappingHandler;
        $this->subscribeProductHandler = $subscribeProductHandler;
        $this->attributeBuilder = $attributeBuilder;
        $this->attributeSaver = $attributeSaver;
        $this->familyBuilder = $familyBuilder;
        $this->familySaver = $familySaver;
        $this->productBuilder = $productBuilder;
        $this->productSaver = $productSaver;
        $this->productRepository = $productRepository;
    }

    /**
     * @Given the product ":identifier" of the family ":familyCode"
     *
     * @param string $identifier
     * @param string $familyCode
     *
     * @throws \Exception
     */
    public function theProductOfTheFamily(string $identifier, string $familyCode): void
    {
        $this->loadProduct($identifier, $familyCode);
    }

    /**
     * @Given a predefined identifiers mapping as follows:
     *
     * @param TableNode $table
     *
     * @throws InvalidMappingException
     */
    public function aPredefinedIdentifiersMapping(TableNode $table): void
    {
        $mappedIdentifiers = $this->getTableNodeAsArrayWithoutHeaders($table);

        $this->saveIdentifiersMapping($mappedIdentifiers);
    }

    /**
     * @Given the product ":identifier" is subscribed to Franklin
     *
     * @param string $identifier
     *
     * @throws InvalidMappingException
     */
    public function theProductIsSubscribedToFranklin(string $identifier): void
    {
        $this->saveIdentifiersMapping(['asin' => 'asin']);

        $product = $this->productRepository->findOneByIdentifier($identifier);

        $command = new SubscribeProductCommand($product->getId());
        $this->subscribeProductHandler->handle($command);
    }

    /**
     * @throws \Exception
     */
    private function loadDefaultCatalog(): void
    {
        $this
            ->getMainContext()
            ->getSubcontext('catalogConfiguration')
            ->aCatalogConfiguration('default');
    }

    /**
     * @param array $mappedIdentifiers
     *
     * @throws InvalidMappingException
     */
    private function saveIdentifiersMapping(array $mappedIdentifiers): void
    {
        $franklinIdentifiers = IdentifiersMapping::FRANKLIN_IDENTIFIERS;

        $emptyIdentifiersMapping = array_fill_keys($franklinIdentifiers, null);
        $identifiersMapping = array_merge($emptyIdentifiersMapping, $mappedIdentifiers);

        $updateIdentifierCommand = new SaveIdentifiersMappingCommand($identifiersMapping);
        $this->saveIdentifiersMappingHandler->handle($updateIdentifierCommand);
    }

    /**
     * Loads a product with its family and attributes
     * Fixture content is in a file in Resources/config/fixtures/products/.
     *
     * @param string $identifier
     * @param string $familyCode
     */
    private function loadProduct(string $identifier, string $familyCode): void
    {
        $this->loadFamily($familyCode);

        $data = $this->loadJsonFileAsArray(sprintf('products/product-%s-%s.json', $familyCode, $identifier));

        $this->productBuilder->withIdentifier($identifier)->withFamily($familyCode);

        foreach ($data['values'] as $attrCode => $value) {
            $this->productBuilder->withValue($attrCode, $value[0]['data']);
        }
        $product = $this->productBuilder->build();

        $this->productSaver->save($product);
    }

    /**
     * Loads the family with its attributes
     * Fixture content is in a file in Resources/config/fixtures/families/.
     *
     * @param string $familyCode
     */
    private function loadFamily(string $familyCode): void
    {
        $normalizedFamily = $this->loadJsonFileAsArray(sprintf('families/family-%s.json', $familyCode));

        $this->loadAttributes($normalizedFamily['attributes']);

        $family = $this->familyBuilder->build($normalizedFamily);

        $this->familySaver->save($family);
    }

    /**
     * Loads attributes according to a provided list of attribute codes.
     * Fixture content is in a file in "Resources/config/fixtures/attributes/".
     *
     * SKU is always skipped, as it is already present in the "minimal" catalog, installed before each test.
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
            $attributes[] = $this->attributeBuilder->build($normalizedAttributes[$attributeCode]);
        }
        $this->attributeSaver->saveAll($attributes);
    }

    /**
     * Loads a file containing json content and return it as a PHP array.
     *
     * @param string $filePath
     *
     * @return array
     */
    private function loadJsonFileAsArray(string $filePath)
    {
        $filePath = realpath(sprintf(__DIR__ . '/../../Acceptance/Resources/fixtures/%s', $filePath));
        Assert::true(file_exists($filePath));
        $jsonContent = file_get_contents($filePath);

        return json_decode($jsonContent, true);
    }

    /**
     * @param TableNode $tableNode
     *
     * @return array
     */
    private function getTableNodeAsArrayWithoutHeaders(TableNode $tableNode): array
    {
        $extractedData = $tableNode->getRowsHash();
        array_shift($extractedData);

        $identifiersMapping = array_fill_keys(IdentifiersMapping::FRANKLIN_IDENTIFIERS, null);

        return array_merge($identifiersMapping, $extractedData);
    }
}
