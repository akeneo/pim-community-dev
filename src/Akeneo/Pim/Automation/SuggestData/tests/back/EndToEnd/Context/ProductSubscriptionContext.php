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

namespace Akeneo\Test\Pim\Automation\SuggestData\EndToEnd\Context;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Context\Traits\ClosestTrait;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionContext extends PimContext
{
    use ClosestTrait;
    use SpinCapableTrait;

    /** @var UpdateIdentifiersMappingHandler */
    private $updateIdentifiersMappingHandler;

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

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param string                                 $mainContextClass
     * @param UpdateIdentifiersMappingHandler        $updateIdentifiersMappingHandler
     * @param EntityBuilder                          $attributeBuilder
     * @param BulkSaverInterface                     $attributeSaver
     * @param EntityBuilder                          $familyBuilder
     * @param SaverInterface                         $familySaver
     * @param Builder\Product                        $productBuilder
     * @param SaverInterface                         $productSaver
     * @param ProductRepositoryInterface             $productRepository
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        string $mainContextClass,
        UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler,
        EntityBuilder $attributeBuilder,
        BulkSaverInterface $attributeSaver,
        EntityBuilder $familyBuilder,
        SaverInterface $familySaver,
        Builder\Product $productBuilder,
        SaverInterface $productSaver,
        ProductRepositoryInterface $productRepository,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository
    ) {
        parent::__construct($mainContextClass);

        $this->updateIdentifiersMappingHandler = $updateIdentifiersMappingHandler;
        $this->attributeBuilder = $attributeBuilder;
        $this->attributeSaver = $attributeSaver;
        $this->familyBuilder = $familyBuilder;
        $this->familySaver = $familySaver;
        $this->productBuilder = $productBuilder;
        $this->productSaver = $productSaver;
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
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
        $this->loadDefaultCatalog();
        $this->loadProduct($identifier, $familyCode);
    }

    /**
     * @Given a predefined mapping as follows:
     *
     * @param TableNode $table
     *
     * @throws InvalidMappingException
     */
    public function aPredefinedMapping(TableNode $table): void
    {
        $mapped = $this->getTableNodeAsArrayWithoutHeaders($table);
        $identifiers = IdentifiersMapping::PIM_AI_IDENTIFIERS;

        $emptyMappedIdentifiers = array_fill_keys($identifiers, null);
        $mappedIdentifiers = array_merge($emptyMappedIdentifiers, $mapped);

        $updateIdentifierCommand = new UpdateIdentifiersMappingCommand($mappedIdentifiers);
        $this->updateIdentifiersMappingHandler->handle($updateIdentifierCommand);
    }

    /**
     * @When I subscribe the product :identifier to PIM.ai
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function iSubscribeTheProductToPimAi(string $identifier): void
    {
        $this->loginAsAdmin();
        $this->subscribeProductToPimAi($identifier);
    }

    /**
     * @Then /^the product "([^"]*)" should be subscribed$/
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function theProductShouldBeSubscribed(string $identifier): void
    {
        $this->checkSubscriptionIsSaved($identifier);
        $this->checkStatusIsEnable();
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

    private function loginAsAdmin(): void
    {
        $this->getNavigationContext()->iAmLoggedInAs('admin', 'admin');
    }

    /**
     * Loads a product with its family and attributes
     * Fixture content is in a file in Resources/config/fixtures/products/
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
     * Fixture content is in a file in Resources/config/fixtures/families/
     *
     * @param string $familyCode
     */
    private function loadFamily(string $familyCode): void
    {
        $this->loadFamilyAttributes($familyCode);

        $data = $this->loadJsonFileAsArray(sprintf('families/family-%s.json', $familyCode));
        $family = $this->familyBuilder->build($data);

        $this->familySaver->save($family);
    }

    /**
     * Loads attributes for a specific family and a default attribute group
     * Fixture content is in a file in Resources/config/fixtures/attributes/
     *
     * @param string $familyCode
     */
    private function loadFamilyAttributes(string $familyCode): void
    {
        $data = $this->loadJsonFileAsArray(sprintf('attributes/attributes-family-%s.json', $familyCode));

        $attributes = [];
        foreach ($data as $rowData) {
            if ('sku' === $rowData['code']) {
                continue;
            }
            $attributes[] = $this->attributeBuilder->build($rowData);
        }
        $this->attributeSaver->saveAll($attributes);
    }

    /**
     * Loads a file containing json content and return it as a PHP array
     *
     * @param string $filePath
     *
     * @return array
     */
    private function loadJsonFileAsArray(string $filePath)
    {
        $filePath = realpath(sprintf(__DIR__ .'/../../Acceptance/Resources/fixtures/%s', $filePath));
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

        $identifiersMapping = array_fill_keys(IdentifiersMapping::PIM_AI_IDENTIFIERS, null);

        return array_merge($identifiersMapping, $extractedData);
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function subscribeProductToPimAi(string $identifier): void
    {
        $this->getNavigationContext()->iAmOnTheEntityEditPage($identifier, 'product');

        $this->spin(function () use ($identifier): bool {
            $nodeElement = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status');
            if (null === $nodeElement) {
                return false;
            }

            $dropDown = $this->getClosest($nodeElement, 'AknDropdown');
            if (null === $dropDown) {
                return false;
            }

            $dropDown->click();
            $button = $dropDown->find('css', '.AknDropdown-menuLink[data-status="enabled"]');
            if (null === $button) {
                return false;
            }
            $button->click();

            return true;
        }, sprintf('Cannot subscribe product "%s" to PIM.ai.', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function checkSubscriptionIsSaved(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $this->spin(function () use ($product) {
            $productSubscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());

            return $productSubscription instanceof ProductSubscription;
        }, sprintf('Cannot find any subscription for product "%s".', $product->getIdentifier()));
    }

    /**
     * @throws \Context\Spin\TimeoutException
     */
    private function checkStatusIsEnable()
    {
        $status = $this->spin(function (): ?NodeElement {
            if (null === $status = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status')) {
                return null;
            }

            return $status;
        }, 'Impossible to find the subscription status.');

        Assert::same('Enabled', $status->getText());
    }
}
