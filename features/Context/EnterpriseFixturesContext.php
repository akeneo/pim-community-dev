<?php

namespace Context;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\Classification\Repository\TagRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Behat\ChainedStepsExtension\Step;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Context\FixturesContext as BaseFixturesContext;
use Context\Spin\SpinCapableTrait;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Command\GenerateMissingVariationFilesCommand;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Model\Tag;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Factory\ProductDraftFactory;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Parser;

/**
 * A context for creating entities
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFixturesContext extends BaseFixturesContext
{
    use SpinCapableTrait;

    protected $enterpriseEntities = [
        'Published'     => 'PimEnterprise\Component\Workflow\Model\PublishedProduct',
        'AssetCategory' => 'PimEnterprise\Component\ProductAsset\Model\Category',
        'User'          => 'PimEnterprise\Bundle\UserBundle\Entity\User',
        'JobProfile'    => 'Akeneo\Component\Batch\Model\JobInstance',
    ];

    /**
     * @param string $userGroup
     * @param string $accessLevel
     * @param string $attributeGroup
     *
     * @Given /^user group "([^"]*)" has the permission to (view|edit) the attribute group "([^"]*)"$/
     */
    public function userGroupHasThePermissionToEditTheAttributeGroup($userGroup, $accessLevel, $attributeGroup)
    {
        $userGroup = $this->getUserGroup($userGroup);
        $attributeGroup = $this->getAttributeGroup($attributeGroup);

        $this
            ->getAccessManager('attribute_group')
            ->setAccess($attributeGroup, [$userGroup], $accessLevel === 'edit' ? [$userGroup] : []);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product drafts:$/
     */
    public function theFollowingProductDrafts(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data = array_merge(
                [
                    'locale' => 'en_US',
                    'status' => 'in progress'
                ],
                $data
            );
            $product = $this->getProduct($data['product']);
            $product->setLocale($data['locale']);

            $productDraft = $this->getProductDraftFactory()->createProductDraft(
                $product,
                $data['author'],
                []
            );
            if (isset($data['createdAt'])) {
                $productDraft->setCreatedAt(new \DateTime($data['createdAt']));
            }

            if (isset($data['result'])) {
                $changes = json_decode($data['result'], true);
                if (null === $changes) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Draft changeset for product "%s" proposed by "%s" is not valid JSON',
                            $data['product'],
                            $data['author']
                        )
                    );
                }

                $productDraft->setChanges($changes);
            }

            if ('ready' === $data['status']) {
                $productDraft->markAsReady();
                $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_TO_REVIEW);
            } else {
                $productDraft->markAsInProgress();
                $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_DRAFT);
            }

            $this->getContainer()->get('pimee_workflow.saver.product_draft')->save($productDraft);
        }
    }

    /**
     * @param string    $username
     * @param string    $product
     * @param TableNode $table
     * @param bool      $scopable
     * @param bool      $ready
     * @param string    $comment
     *
     * @return Step\Given[]
     * @Given /^(\w+) proposed the following change to "([^"]*)":$/
     */
    public function someoneProposedTheFollowingChangeTo(
        $username,
        $product,
        TableNode $table,
        $scopable = false,
        $ready = true,
        $comment = null
    ) {
        $steps = [
            new Step\Given(sprintf('I am logged in as "%s"', $username)),
            new Step\Given(sprintf('I edit the "%s" product', $product)),
        ];

        foreach ($table->getHash() as $data) {
            $data = array_merge(
                ['tab' => '', 'locale' => '', 'scope' => ''],
                $data
            );
            if ('' !== $data['tab']) {
                $steps[] = new Step\Given(sprintf('I visit the "%s" group', $data['tab']));
            }
            if ('' !== $data['locale']) {
                $steps[] = new Step\Given(sprintf('I switch the locale to "%s"', $data['locale']));
            }
            if ('' !== $data['scope']) {
                $steps[] = new Step\Given(sprintf('I switch the scope to "%s"', $data['scope']));
            }
            if ($scopable) {
                $steps[] = new Step\Given(
                    sprintf('I expand the "%s" attribute', substr(strstr($data['field'], ' '), 1))
                );
            }
            switch (true) {
                case 0 === strpos($data['value'], 'file('):
                    $file = strtr($data['value'], ['file(' => '', ')' => '']);
                    $steps[] = new Step\Given(sprintf('I attach file "%s" to "%s"', $file, $data['field']));

                    break;
                case 0 === strpos($data['value'], 'state('):
                    $steps[] = new Step\Given(sprintf('I check the "%s" switch', $data['field']));

                    break;
                default:
                    $steps[] = new Step\Given(sprintf('I change the "%s" to "%s"', $data['field'], $data['value']));
            }
        }

        $steps[] = new Step\Given('I save the product');
        if ($ready) {
            $steps[] = new Step\Given('I should not see the text "There are unsaved changes."');
            $steps[] = new Step\Given('I press the "Send for approval" button');

            if (null !== $comment) {
                $steps[] = new Step\Given(sprintf('I fill in this comment in the popin: "%s"', $comment));
            }

            $steps[] = new Step\Given('I press the "Send" button in the popin');
        }
        $steps[] = new Step\Given('I logout');

        return $steps;
    }

    /**
     * @param string    $username
     * @param string    $product
     * @param string    $comment
     * @param TableNode $table
     *
     * @return Step\Given[]
     * @Given /^(\w+) proposed the following change to "([^"]*)" with the comment "([^"]+)":$/
     */
    public function someoneProposedTheFollowingChangeToWithComment($username, $product, $comment, TableNode $table)
    {
        return $this->someoneProposedTheFollowingChangeTo($username, $product, $table, false, true, $comment);
    }

    /**
     * @param string    $username
     * @param string    $product
     * @param TableNode $table
     *
     * @return Step\Given[]
     * @Given /^(\w+) proposed the following scopable change to "([^"]*)":$/
     */
    public function someoneProposedTheFollowingScopableChangeTo($username, $product, TableNode $table)
    {
        return $this->someoneProposedTheFollowingChangeTo($username, $product, $table, true);
    }

    /**
     * @param string    $username
     * @param string    $product
     * @param TableNode $table
     *
     * @return Step\Given[]
     * @Given /^(\w+) started to propose the following change to "([^"]*)":$/
     */
    public function someoneStartedToProposeTheFollowingChangeTo($username, $product, TableNode $table)
    {
        return $this->someoneProposedTheFollowingChangeTo($username, $product, $table, false, false);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute group accesses:$/
     */
    public function theFollowingAttributeGroupAccesses(TableNode $table)
    {
        $this->createAccesses($table, 'attribute group');
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following (.*) category accesses:$/
     */
    public function theFollowingCategoryAccesses($categoryType, TableNode $table)
    {
        $this->createAccesses($table, sprintf('%s category', $categoryType));
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following locale accesses:$/
     */
    public function theFollowingLocaleAccesses(TableNode $table)
    {
        $this->createAccesses($table, 'locale');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities()
    {
        return array_merge($this->entities, $this->enterpriseEntities);
    }

    /**
     * @param string $sku
     *
     * @throws \InvalidArgumentException
     *
     * @return PublishedProductInterface
     */
    public function getPublishedProduct($sku)
    {
        $published = $this->getPublishedProductManager()->findByIdentifier($sku);

        if (!$published) {
            throw new \InvalidArgumentException(sprintf('Could not find a published product with sku "%s"', $sku));
        }

        return $published;
    }

    /**
     * @param string $sku
     *
     * @throws \InvalidArgumentException
     *
     * @return PublishedProductInterface
     */
    public function getPublishedByOriginal($sku)
    {
        $originalProduct = $this->getProduct($sku);
        $published       = $this->getPublishedProductManager()->findPublishedProductByOriginal($originalProduct);

        if (!$published) {
            throw new \InvalidArgumentException(sprintf('Could not find a published product with sku "%s"', $sku));
        }

        return $published;
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return ProductDraft
     */
    public function getProductDraft(ProductInterface $product, $username)
    {
        $productDraft = $this->getProposalRepository()->findUserProductDraft($product, $username);

        if ($productDraft) {
            $this->refresh($productDraft);
        }

        return $productDraft;
    }

    /**
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Then /^attribute (\w+) of published "([^"]*)" should be "([^"]*)"$/
     */
    public function attributeOfPublishedShouldBe($attribute, $identifier, $value)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $productValue = $this->getPublishedProductValue($identifier, strtolower($attribute));
        $this->assertDataEquals($productValue->getData(), $value);
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) proposals?$/
     */
    public function thereShouldBeProposals($expectedTotal)
    {
        $total = count($this->getProposalRepository()->findAll());

        assertEquals($expectedTotal, $total);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following assets?:$/
     */
    public function theFollowingAssets(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createAsset($data);
        }
    }

    /**
     * @param array|string $data
     *
     * @throws \Exception
     *
     * @return AssetInterface
     *
     * @Given /^a "([^"]+)" asset$/
     */
    public function createAsset($data)
    {
        $asset = new Asset();

        if (is_string($data)) {
            $asset->setCode($data);
        } else {
            if (isset($data['code']) && is_string($data['code'])) {
                $asset->setCode($data['code']);
            }
            if (isset($data['description'])) {
                $asset->setDescription($data['description']);
            }
            if (isset($data['enabled']) && in_array($data['enabled'], ['yes', 'no'])) {
                $isEnabled = ('yes' === $data['enabled']);
                $asset->setEnabled($isEnabled);
            }
            if (isset($data['tags'])) {
                $tags = explode(',', $data['tags']);
                foreach ($tags as $code) {
                    $tag = $this->createTag(trim($code));
                    $asset->addTag($tag);
                }
            }
            if (isset($data['categories']) && '' !== $data['categories']) {
                $categories = explode(',', $data['categories']);
                foreach ($categories as $code) {
                    $category = $this->getAssetCategoryRepository()->findOneByIdentifier(trim($code));
                    if (null === $category) {
                        throw new \Exception("\"$code\" category not found");
                    }
                    $asset->addCategory($category);
                }
            }
            if (isset($data['end of use at'])) {
                $endDate = new \DateTime($data['end of use at']);
                $asset->setEndOfUseAt($endDate);
            }
            if (isset($data['created at'])) {
                $created = new \DateTime($data['created at']);
            } else {
                $created = new \DateTime('now');
            }
            $asset->setCreatedAt($created);
            if (isset($data['updated at'])) {
                $updated = new \DateTime($data['updated at']);
            } else {
                $updated = new \DateTime('now');
            }
            $asset->setUpdatedAt($updated);

            // TODO: References and variations
        }

        $this->getAssetSaver()->save($asset);

        return $asset;
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following tags?:$/
     */
    public function theFollowingTags(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createTag($data);
        }
    }

    /**
     * @param string|array $data
     *
     * @throws \Exception
     *
     * @return TagInterface
     *
     *
     * @Given /^a "([^"]+)" tag$/
     */
    public function createTag($data)
    {
        if (is_string($data)) {
            $code = $data;
        } elseif (isset($data['code'])) {
            $code = $data['code'];
        } else {
            throw new \Exception('A tag must have a code.');
        }

        $repo = $this->getTagRepository();
        $tag  = $repo->findOneByIdentifier($code);

        if (null === $tag) {
            $tag = new Tag();
            $tag->setCode($code);
            $this->getAssetTagSaver()->save($tag);
        }

        return $tag;
    }

    /**
     * @Then /^the asset "([^"]*)" should have the following values:$/
     */
    public function theAssetShouldHaveTheFollowingValues($identifier, TableNode $table)
    {
        $this->clearUOW();
        $asset = $this->getAssetRepository()->findOneByIdentifier($identifier);

        foreach ($table->getRowsHash() as $rawCode => $expectedValue) {
            $getter = 'get' . ucfirst($rawCode);
            $assetValue = $asset->$getter();

            switch ($rawCode) {
                case 'description':
                    if ('' === $expectedValue) {
                        assertEmpty((string) $assetValue);
                    } else {
                        assertEquals($expectedValue, $assetValue);
                    }
                    break;
                case 'tags':
                    if ('' === $expectedValue) {
                        assertEquals([], $assetValue->toArray());
                    } else {
                        $expectedValue = explode(',', $expectedValue);
                        $tags = array_map(function ($tag) {
                            return $tag->getCode();
                        }, $assetValue->toArray());
                        assertTrue(0 === count(array_diff($expectedValue, $tags)));
                    }
                    break;
                case 'endOfUseAt':
                    if ('' === $expectedValue) {
                        assertEquals(null, $assetValue);
                    } else {
                        assertEquals($expectedValue, $assetValue->format('Y-m-d'));
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Could not find value "%s" for asset with code "%s"',
                            $rawCode,
                            $identifier
                        )
                    );
            }
        }
    }

    /**
     * @param string $identifier
     * @param string $attribute
     * @param string $locale
     * @param string $scope
     *
     * @throws \InvalidArgumentException
     *
     * @return \Pim\Component\Catalog\Model\ValueInterface
     */
    protected function getPublishedProductValue($identifier, $attribute, $locale = null, $scope = null)
    {
        if (null === $product = $this->getPublishedByOriginal($identifier)) {
            throw new \InvalidArgumentException(sprintf('Could not find published product with original identifier "%s"', $identifier));
        }

        if (null === $value = $product->getValue($attribute, $locale, $scope)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find product value for attribute "%s" in locale "%s" for scope "%s"',
                    $attribute,
                    $locale,
                    $scope
                )
            );
        }

        return $value;
    }

    /**
     * @param $code
     *
     * @throws \InvalidArgumentException
     *
     * @return AssetInterface
     */
    public function getAsset($code)
    {
        $asset = $this->spin(function () use ($code) {
            return $this->getAssetRepository()->findOneByIdentifier($code);
        }, sprintf('Could not find a product asset with code "%s"', $code));

        $this->refresh($asset);

        return $asset;
    }

    /**
     * @param $code
     *
     * @throws \InvalidArgumentException
     *
     * @return TagInterface
     */
    public function getTag($code)
    {
        $tag = $this->getTagRepository()->findOneByIdentifier($code);

        if (null === $tag) {
            throw new \InvalidArgumentException(sprintf('Could not find a tag with code "%s"', $code));
        }

        $this->refresh($tag);

        return $tag;
    }

    /**
     * @param $code
     *
     * @throws \InvalidArgumentException
     *
     * @return CategoryInterface
     */
    public function getAssetCategory($code)
    {
        $assetCategory = $this->spin(function () use ($code) {
            return $this->getAssetCategoryRepository()->findOneByIdentifier($code);
        }, sprintf('Could not find a category with code "%s"', $code));

        $this->refresh($assetCategory);

        return $assetCategory;
    }

    /**
     * @Given /^I should see the following proposals on the widget:$/
     *
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingProposalsOnTheWidget(TableNode $table)
    {
        $expectedProposals = $table->getHash();
        $this->spin(function () use ($expectedProposals) {
            $actualProposals = $this->getCurrentPage()->getElement('Proposal widget')->getProposalsToReview();

            return $expectedProposals == $actualProposals;
        }, sprintf('Failed to find the following proposals "%s"', print_r($expectedProposals, true)));
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following published products?:$/
     */
    public function theFollowingPublishedProduct(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createPublishedProduct($data);
        }
    }

    /**
     * @param string    $username
     * @param string    $products
     * @param TableNode $table
     *
     * @return Step\Given[]
     * @Given /^(\w+) should have proposed the following values for products (.*):$/
     */
    public function someoneShouldHaveProposedTheFollowingValuesForProducts($username, $products, TableNode $table)
    {
        $steps = [];
        foreach ($this->listToArray($products) as $product) {
            $steps[] = new Step\Given(sprintf('I edit the "%s" product', $product));

            foreach ($table->getHash() as $data) {
                $steps[] = new Step\Given(
                    sprintf('the field %s should contain "%s"', $data['attribute'], $data['value'])
                );
                $steps[] = new Step\Given(sprintf('I should see that %s is a modified value', $data['attribute']));
            }
        }

        return $steps;
    }

    /**
     * @param string $username
     *
     * @Then /^the user "([^"]*)" should have email notifications enabled$/
     */
    public function userShouldHaveEmailNotificationsEnabled($username)
    {
        return $this->userShouldHaveEmailNotifications($username, true);
    }

    /**
     * @param string $username
     *
     * @Then /^the user "([^"]*)" should have email notifications disabled$/
     */
    public function userShouldHaveEmailNotificationsDisabled($username)
    {
        return $this->userShouldHaveEmailNotifications($username, false);
    }

    /**
     * @param $username
     * @param $value
     *
     * @throws Spin\TimeoutException
     */
    protected function userShouldHaveEmailNotifications($username, $value)
    {
        $user = $this->getUser($username);
        $this->spin(function () use ($user, $value) {
            $this->getEntityManager()->refresh($user);
            $emailNotifications = (bool) $user->isEmailNotifications();

            return $emailNotifications === $value;
        }, sprintf('Email notifications of %s does not change to %s.', $username, $value ? 'true' : 'false'));
    }

    /**
     * @param string $username
     * @param int    $delay
     *
     * @Then /^the user "([^"]*)" should have an asset delay notification set to (\d+)$/
     */
    public function userShouldHaveAnAssetDelayNotification($username, $delay)
    {
        $user = $this->getUser($username);
        $value = $this->spin(function () use ($user, $delay) {
            $this->getEntityManager()->refresh($user);
            $value = $user->getAssetDelayReminder();

            return (int) $value === (int) $delay ? $value : null;
        }, sprintf('Asset delay reminder of %s does not change to %s', $username, $delay));

        assertEquals($value, $delay);
    }

    /**
     * @param mixed $data
     *
     * @return PublishedProductInterface
     */
    protected function createPublishedProduct($data)
    {
        $product = $this->createProduct($data);

        return $this->getPublishedProductManager()->publish($product);
    }

    /**
     * @param string $type
     *
     * @return AttributeGroupAccessManager|CategoryAccessManager
     */
    protected function getAccessManager($type)
    {
        if (in_array($type, ['product category'])) {
            return $this->getContainer()->get('pimee_security.manager.category_access');
        }

        if (in_array($type, ['asset category'])) {
            return $this->getContainer()->get('pimee_product_asset.manager.category_access');
        }

        return $this->getContainer()->get(sprintf('pimee_security.manager.%s_access', str_replace(' ', '_', $type)));
    }

    /**
     * @return ProductDraftFactory
     */
    protected function getRuleDefinitionProcessor()
    {
        return $this->getContainer()->get('pimee_catalog_rule.processor.denormalization.rule_definition');
    }

    /**
     * @return ProductDraftFactory
     */
    protected function getProductDraftFactory()
    {
        return $this->getContainer()->get('pimee_workflow.factory.product_draft');
    }

    /**
     * @return CategoryRepositoryInterface
     */
    protected function getProductCategoryRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.category');
    }

    /**
     * @return CategoryRepositoryInterface
     */
    protected function getAssetCategoryRepository()
    {
        return $this->getContainer()->get('pimee_product_asset.repository.category');
    }

    /**
     * @return TagRepositoryInterface
     */
    protected function getTagRepository()
    {
        return $this->getContainer()->get('pimee_product_asset.repository.tag');
    }

    /**
     * @return AssetRepositoryInterface
     */
    protected function getAssetRepository()
    {
        return $this->getContainer()->get('pimee_product_asset.repository.asset');
    }

    /**
     * @return LocaleRepositoryInterface
     */
    public function getLocaleRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.locale');
    }

    /**
     * @return ChannelRepositoryInterface
     */
    public function getChannelRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.channel');
    }

    /**
     * @return PublishedProductManager
     */
    protected function getPublishedProductManager()
    {
        return $this->getContainer()->get('pimee_workflow.manager.published_product');
    }

    /**
     * Get the access level according to the access type and action (view or edit)
     *
     * @param string $type
     * @param string $action
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getAccessLevelByAccessTypeAndAction($type, $action)
    {
        if ('none' === $action) {
            return $action;
        }

        if ('attribute group' === $type) {
            return ($action === 'edit') ? Attributes::EDIT_ATTRIBUTES : Attributes::VIEW_ATTRIBUTES;
        }

        if (in_array($type, ['product category', 'asset category', 'locale'])) {
            switch ($action) {
                case 'own':
                    return Attributes::OWN_PRODUCTS;
                case 'edit':
                    return Attributes::EDIT_ITEMS;
                case 'view':
                default:
                    return Attributes::VIEW_ITEMS;
            }
        }

        if (in_array($type, ['job profile'])) {
            switch ($action) {
                case 'edit':
                    return Attributes::EDIT;
                case 'execute':
                default:
                    return Attributes::EXECUTE;
            }
        }

        throw new \Exception('Undefined access type');
    }

    /**
     * Create accesses
     *
     * @param TableNode $table
     * @param string    $accessType
     */
    protected function createAccesses(TableNode $table, $accessType)
    {
        $accessClass = str_replace(' ', '', ucwords($accessType));
        $getterAccessType = sprintf('get%s', $accessClass);

        $accessManager = $this->getAccessManager($accessType);
        foreach ($table->getHash() as $data) {
            $access = $this->$getterAccessType($data[$accessType]);
            $accessManager->revokeAccess($access);
        }

        foreach ($table->getHash() as $data) {
            $access = $this->$getterAccessType($data[$accessType]);
            $userGroup = $this->getUserGroup($data['user group']);
            $accessLevel = $this->getAccessLevelByAccessTypeAndAction($accessType, $data['access']);

            if ('none' != $accessLevel) {
                $accessManager->grantAccess($access, $userGroup, $accessLevel);
            }
        }

        $registry = $this->getEntityManager();
        $registry->flush();
    }

    /**
     * @param PyStringNode $string
     *
     * @Given /^the following product rule definitions:$/
     */
    public function theFollowingProductRuleDefinitions(PyStringNode $string)
    {
        $definitions = (new Parser())->parse($this->replacePlaceholders($string));

        foreach ($definitions as $key => $definition) {
            $definition['code'] = $key;

            $ruleDefinition = $this->getRuleDefinitionProcessor()->process($definition);
            $manager = $this->getRuleSaver();
            $manager->save($ruleDefinition);
        }
    }

    /**
     * @param string $code
     *
     * @throws \InvalidArgumentException
     *
     * @return RuleDefinitionInterface
     */
    public function getRule($code)
    {
        $rule = $this->getRuleDefinitionRepository()->findOneByCode($code);
        if (!$rule) {
            throw new \InvalidArgumentException(sprintf('Could not find a rule with code "%s"', $code));
        }

        return $rule;
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following assets?:$/
     */
    public function thereShouldBeTheFollowingAssets(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $asset = $this->getAsset($data['code']);
            $this->refresh($asset);

            assertEquals($data['code'], $asset->getCode());
            if (array_key_exists('description', $data)) {
                assertEquals($data['description'], $asset->getDescription());
            }

            if (array_key_exists('categories', $data)) {
                assertEquals($data['categories'], implode(',', $asset->getCategoryCodes()));
            }

            if (array_key_exists('tags', $data)) {
                assertEquals($data['tags'], implode(',', $asset->getTagCodes()));
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following tags?:$/
     */
    public function thereShouldBeTheFollowingTags(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $tag = $this->getTag($data['code']);
            $this->refresh($tag);

            assertEquals($data['code'], $tag->getCode());
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following assets categories:$/
     */
    public function thereShouldBeTheFollowingAssetsCategories(TableNode $table)
    {
        $this->getEntityManager()->clear();

        foreach ($table->getHash() as $data) {
            $assetCategory = $this->getAssetCategory($data['code']);
            $this->refresh($assetCategory);

            if (isset($data['label-en_US'])) {
                assertEquals($data['label-en_US'], $assetCategory->getTranslation('en_US')->getLabel());
            }

            if (isset($data['label-fr_FR'])) {
                assertEquals($data['label-fr_FR'], $assetCategory->getTranslation('fr_FR')->getLabel());
            }

            if (isset($data['label-de_DE'])) {
                assertEquals($data['label-de_DE'], $assetCategory->getTranslation('de_DE')->getLabel());
            }

            if (empty($data['parent'])) {
                assertNull($assetCategory->getParent());
            } else {
                assertEquals($data['parent'], $assetCategory->getParent()->getCode());
            }
        }
    }

    /**
     * @Given /^the missing product asset variations have been generated$/
     */
    public function theMissingVariationsHaveBeenGenerated()
    {
        $application = new Application();
        $application->add(new GenerateMissingVariationFilesCommand());

        $generateCommand = $application->find('pim:asset:generate-missing-variation-files');
        $generateCommand->setContainer($this->getContainer());
        $generateCommandTester = new CommandTester($generateCommand);

        $generateCommandTester->execute(
            [
                'command' => $generateCommand->getName(),
            ]
        );
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following assets categor(?:y|ies):$/
     */
    public function theFollowingAssetsCategories(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createAssetCategory($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function theFollowingJobs(TableNode $table)
    {
        parent::theFollowingJobs($table);

        $accesses = [['job profile', 'user group', 'access']];

        $rows = $table->getRows();
        array_shift($rows);

        foreach ($rows as $row) {
            foreach (['IT support', 'Manager', 'Redactor'] as $role) {
                $accesses[] = [$row[3], $role, 'execute'];
                $accesses[] = [$row[3], $role, 'edit'];
            }
        }

        $accesses = new TableNode($accesses);

        $this->createAccesses($accesses, 'job profile');
    }

    /**
     * {@inheritdoc}
     */
    public function theFollowingCategories(TableNode $table)
    {
        parent::theFollowingCategories($table);

        $accesses = [['product category', 'user group', 'access']];

        $rows = $table->getRows();
        array_shift($rows);

        $defaultUserGroup = $this->getContainer()->get('pim_user.repository.group')->getDefaultUserGroup();
        foreach ($rows as $row) {
            $accesses[] = [$row[0], $defaultUserGroup->getName(), 'own'];
        }

        $accesses = new TableNode($accesses);

        $this->createAccesses($accesses, 'product category');
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following assets tag(?:|s):$/
     */
    public function theFollowingAssetsTags(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createAssetTag($data);
        }
    }

    /**
     * @param array|string $data
     *
     * @return CategoryInterface
     */
    protected function createAssetCategory($data)
    {
        if (is_string($data)) {
            $data = [['code' => $data]];
        }

        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.category');
        $processor = $this->getContainer()->get('pimee_product_asset.processor.denormalization.category');
        $convertedData = $converter->convert($data);
        $category = $processor->process($convertedData);

        /*
         * When using ODM, one must persist and flush category without product
         * before adding and persisting products inside it
         */
        $assets = $category->getAssets();
        $this->getContainer()->get('pimee_product_asset.saver.category')->save($category);
        foreach ($assets as $asset) {
            $asset->addCategory($category);
            $this->getContainer()->get('pimee_product_asset.saver.asset')->save($asset);
        }

        return $category;
    }

    /**
     * @param array|string $data
     *
     * @return TagInterface
     */
    protected function createAssetTag($data)
    {
        if (is_string($data)) {
            $data = [['code' => $data]];
        }

        $processor = $this->getContainer()->get('pimee_product_asset.processor.denormalization.tag');
        $tag       = $processor->process($data);

        $this->getContainer()->get('pimee_product_asset.saver.tag')->save($tag);

        return $tag;
    }

    /**
     * @return RuleDefinitionRepositoryInterface
     */
    protected function getRuleDefinitionRepository()
    {
        return $this->getContainer()->get('akeneo_rule_engine.repository.rule_definition');
    }

    /**
     * @return SaverInterface
     */
    protected function getRuleSaver()
    {
        return $this->getContainer()->get('akeneo_rule_engine.saver.rule_definition');
    }

    /**
     * @return SaverInterface
     */
    protected function getAssetSaver()
    {
        return $this->getContainer()->get('pimee_product_asset.saver.asset');
    }

    /**
     * @return SaverInterface
     */
    protected function getAssetTagSaver()
    {
        return $this->getContainer()->get('pimee_product_asset.saver.tag');
    }

    /**
     * @return ProductDraftRepositoryInterface
     */
    protected function getProposalRepository()
    {
        return $this->getContainer()->get('pimee_workflow.repository.product_draft');
    }

    /**
     * @return ProductRepositoryInterface
     */
    protected function getProductRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.product_without_permission');
    }
}
