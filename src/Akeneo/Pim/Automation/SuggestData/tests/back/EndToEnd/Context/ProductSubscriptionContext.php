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
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
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

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param string                                 $mainContextClass
     * @param UpdateIdentifiersMappingHandler        $updateIdentifiersMappingHandler
     * @param SubscribeProductHandler                $subscribeProductHandler
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
        SubscribeProductHandler $subscribeProductHandler,
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
        $this->subscribeProductHandler = $subscribeProductHandler;
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
     *
     * @todo APAI-286: Extract in its own FixtureContext
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
     *
     * @todo APAI-286: Extract in its own FixtureContext
     */
    public function aPredefinedMapping(TableNode $table): void
    {
        $mappedIdentifiers = $this->getTableNodeAsArrayWithoutHeaders($table);

        $this->updateIdentifiersMapping($mappedIdentifiers);
    }

    /**
     * @Given the product ":identifier" is subscribed to PIM.ai
     *
     * @param string $identifier
     *
     * @throws InvalidMappingException
     *
     * @todo APAI-286: Extract in its own FixtureContext
     */
    public function theProductIsSubscribedToPimAi(string $identifier): void
    {
        $this->updateIdentifiersMapping(['asin' => 'asin']);

        $product = $this->productRepository->findOneByIdentifier($identifier);

        $command = new SubscribeProductCommand($product->getId());
        $this->subscribeProductHandler->handle($command);
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
     * @When I unsubscribe the product :identifier
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function iUnsubscribeTheProductToPimAi(string $identifier): void
    {
        $this->loginAsAdmin();
        $this->unsubscribeProductFromPimAi($identifier);
    }

    /**
     * @Then the product :identifier should be subscribed
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
     * @Then the product :identifier should not be subscribed
     *
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    public function theProductShouldNotBeSubscribed(string $identifier): void
    {
        $this->checkSubscriptionIsNotSaved($identifier);
        $this->checkStatusIsDisabled();
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
     * @param array $mappedIdentifiers
     *
     * @throws InvalidMappingException
     */
    private function updateIdentifiersMapping(array $mappedIdentifiers): void
    {
        $pimAiIdentifiers = IdentifiersMapping::PIM_AI_IDENTIFIERS;

        $emptyIdentifiersMapping = array_fill_keys($pimAiIdentifiers, null);
        $identifiersMapping = array_merge($emptyIdentifiersMapping, $mappedIdentifiers);

        $updateIdentifierCommand = new UpdateIdentifiersMappingCommand($identifiersMapping);
        $this->updateIdentifiersMappingHandler->handle($updateIdentifierCommand);
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
        $dropdown = $this->getActivationDropDown($identifier);

        $this->spin(function () use ($identifier, $dropdown): bool {
            $dropdown->click();
            $button = $dropdown->find('css', '.franklin-subscription-enabled');
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
    private function unsubscribeProductFromPimAi(string $identifier): void
    {
        $dropdown = $this->getActivationDropDown($identifier);

        $this->spin(function () use ($identifier, $dropdown): bool {
            $dropdown->click();
            $button = $dropdown->find('css', '.franklin-subscription-disabled');
            if (null === $button) {
                return false;
            }
            $button->click();

            return true;
        }, sprintf('Cannot unsubscribe product "%s" from PIM.ai.', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     *
     * @return NodeElement
     */
    private function getActivationDropDown(string $identifier): NodeElement
    {
        $this->getNavigationContext()->iAmOnTheEntityEditPage($identifier, 'product');

        return $this->spin(function () use ($identifier): ?NodeElement {
            $nodeElement = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status');
            if (null === $nodeElement) {
                return null;
            }

            $dropdown = $this->getClosest($nodeElement, 'AknDropdown');
            if (null === $dropdown) {
                return null;
            }

            return $dropdown;
        }, sprintf('Cannot find PIM.ai subscription drop-down for product "%s".', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function checkSubscriptionIsSaved(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $this->spin(function () use ($product): bool {
            $productSubscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());

            return $productSubscription instanceof ProductSubscription;
        }, sprintf('Cannot find any subscription for product "%s".', $product->getIdentifier()));
    }

    /**
     * @param string $identifier
     *
     * @throws TimeoutException
     */
    private function checkSubscriptionIsNotSaved(string $identifier): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        $this->spin(function () use ($product): bool {
            $productSubscription = $this->productSubscriptionRepository->findOneByProductId($product->getId());

            return null === $productSubscription;
        }, sprintf('Found a subscription for product "%s" when there should be none.', $product->getIdentifier()));
    }

    /**
     * @throws TimeoutException
     */
    private function checkStatusIsEnable(): void
    {
        $this->spin(function (): bool {
            if (null === $status = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status')) {
                return false;
            }

            return 'Enabled' === $status->getText();
        }, 'The subscription status is not "Enabled".');
    }

    /**
     * @throws TimeoutException
     */
    private function checkStatusIsDisabled(): void
    {
        $this->spin(function (): bool {
            if (null === $status = $this->getCurrentPage()->find('css', '.ask-franklin-subscription-status')) {
                return false;
            }

            return 'Disabled' === $status->getText();
        }, 'The subscription status is not "Disabled".');
    }
}
