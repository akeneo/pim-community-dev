<?php

namespace Context;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Context\FixturesContext as BaseFixturesContext;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Classification\Repository\TagRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Model\Tag;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * A context for creating entities
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFixturesContext extends BaseFixturesContext
{
    protected $enterpriseEntities = [
        'Published'     => 'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct',
        'AssetCategory' => 'PimEnterprise\Component\ProductAsset\Model\Category',
    ];

    /**
     * {@inheritdoc}
     */
    public function createProduct($data)
    {
        if (!is_array($data)) {
            $data = ['sku' => $data];
        }

        return parent::createProduct($data);
    }

    /**
     * @param TableNode $table
     */
    public function theFollowingProductValues(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $row = array_merge(['locale' => null, 'scope' => null, 'value' => null], $row);

            $attributeCode = $row['attribute'];
            if ($row['locale']) {
                $attributeCode .= '-' . $row['locale'];
            }
            if ($row['scope']) {
                $attributeCode .= '-' . $row['scope'];
            }

            $data = [
                'sku'          => $row['product'],
                $attributeCode => $this->replacePlaceholders($row['value'])
            ];

            $this->createProduct($data);
        }

        $this->flush();
    }

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
            $productDraft->setStatus($data['status'] === 'ready' ? ProductDraftInterface::READY : ProductDraftInterface::IN_PROGRESS);
            $manager = $this->getSmartRegistry()->getManagerForClass(get_class($productDraft));

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

            $manager->persist($productDraft);
        }
        $manager->flush();
    }

    /**
     * @param string    $username
     * @param string    $product
     * @param TableNode $table
     * @param bool      $scopable
     * @param bool      $ready
     *
     * @return Step\Given[]
     * @Given /^(\w+) proposed the following change to "([^"]*)":$/
     */
    public function someoneProposedTheFollowingChangeTo(
        $username,
        $product,
        TableNode $table,
        $scopable = false,
        $ready = true
    ) {
        $steps = [
            new Step\Given(sprintf('I am logged in as "%s"', $username)),
            new Step\Given(sprintf('I edit the "%s" product', $product)),
        ];

        foreach ($table->getHash() as $data) {
            $data = array_merge(
                ['tab' => ''],
                $data
            );
            if ('' !== $data['tab']) {
                $steps[] = new Step\Given(sprintf('I visit the "%s" group', $data['tab']));
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
            $steps[] = new Step\Given('I press the "Send for approval" button');
        }
        $steps[] = new Step\Given('I logout');

        return $steps;
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
     * @return \Pim\Bundle\CatalogBundle\Model\Product
     */
    public function getPublished($sku)
    {
        $published = $this->getPublishedProductManager()->findByIdentifier($sku);

        if (!$published) {
            throw new \InvalidArgumentException(sprintf('Could not find a published product with sku "%s"', $sku));
        }

        $this->refresh($published);

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
            if (isset($data['categories'])) {
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
     * @return TagInterface
     *
     * @throws \Exception
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
                        $tags = array_map(function($tag) {
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
     * @param $code
     *
     * @throws \InvalidArgumentException
     *
     * @return AssetInterface
     */
    public function getAsset($code)
    {
        $asset = $this->getAssetRepository()->findOneByIdentifier($code);

        if (null === $asset) {
            throw new \InvalidArgumentException(sprintf('Could not find a product asset with code "%s"', $code));
        }

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
        $assetCategory = $this->getAssetCategoryRepository()->findOneByIdentifier($code);

        if (null === $assetCategory) {
            throw new \InvalidArgumentException(sprintf('Could not find a category with code "%s"', $code));
        }

        $this->refresh($assetCategory);

        return $assetCategory;
    }

    /**
     * @Given /^I should see the following proposals:$/
     *
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingProposals(TableNode $table)
    {
        $expectedProposals = $table->getHash();
        $actualProposals = $this->getSession()->getPage()->findAll('css', '#proposal-widget tbody tr');

        $expectedCount = count($expectedProposals);
        $actualCount   = count($actualProposals);
        if ($expectedCount !== $actualCount) {
            throw new \Exception(
                sprintf(
                    'Expecting %d proposals, actually saw %d',
                    $expectedCount,
                    $actualCount
                )
            );
        }

        foreach ($expectedProposals as $key => $proposal) {
            $cells = $actualProposals[$key]->findAll('css', 'td');
            if ($cells[1]->getText() !== $proposal['author']) {
                throw new \Exception(
                    sprintf(
                        'Proposal #%d author is expected to be "%s", actually is "%s"',
                        $key + 1,
                        $proposal['author'],
                        $cells[1]->getText()
                    )
                );
            }

            if ($cells[2]->getText() !== $proposal['product']) {
                throw new \Exception(
                    sprintf(
                        'Proposal #%d product is expected to be "%s", actually is "%s"',
                        $key + 1,
                        $proposal['product'],
                        $cells[2]->getText()
                    )
                );
            }
        }
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
     * @param mixed $data
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface
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
        if ('product category' === $type) {
            $type = 'category';
        }

        return $this->getContainer()->get(sprintf('pimee_security.manager.%s_access', str_replace(' ', '_', $type)));
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
     * @return \PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager
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

        if ('product category' === $type || 'locale' === $type) {
            return ($action === 'edit') ? Attributes::EDIT_PRODUCTS : Attributes::VIEW_PRODUCTS;
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

        foreach ($table->getHash() as $data) {
            $access = $this->$getterAccessType($data[$accessType]);
            $userGroup = $this->getUserGroup($data['user group']);
            $accessLevel = $this->getAccessLevelByAccessTypeAndAction($accessType, $data['access']);

            $accessManager = $this->getAccessManager($accessType);

            if ('none' === $accessLevel) {
                $viewGroups = $accessManager->getViewUserGroups($access);

                $key = array_search($userGroup, $viewGroups, true);
                if (false !== $key) {
                    unset($viewGroups[$key]);
                }

                $editGroups = $accessManager->getEditUserGroups($access);
                $key = array_search($userGroup, $editGroups, true);
                if (false !== $key) {
                    unset($editGroups[$key]);
                }

                $accessManager->setAccess($access, $viewGroups, $editGroups);
            } else {
                $accessManager->grantAccess($access, $userGroup, $accessLevel);
            }
        }

        $registry = $this->getSmartRegistry()
            ->getManagerForClass(sprintf('PimEnterprise\Bundle\SecurityBundle\Entity\%sAccess', $accessClass));
        $registry->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product rules:$/
     */
    public function theFollowingProductRules(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $rule = new RuleDefinition();
            $rule->setCode($data['code']);
            $rule->setPriority((int) $data['priority']);
            $rule->setType('product');
            // TODO : via EM to avoid validation
            $manager = $this->getSmartRegistry()->getManagerForClass(get_class($rule));
            $manager->persist($rule);
        }
        $manager->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product rule conditions:$/
     */
    public function theFollowingProductRuleConditions(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data = array_merge(
                [
                    'locale' => null,
                    'scope'  => null
                ],
                $data
            );

            $rule = $this->getRule($data['rule']);
            $content = $rule->getContent();
            if (!isset($content['conditions'])) {
                $content['conditions'] = [];
            }

            if ($data['operator'] === 'IN') {
                $data['value'] =  $this->getMainContext()->listToArray($data["value"]);
            }

            $condition = [
                'field'    => $data['field'],
                'operator' => $data['operator'],
                // TODO: replace this dirty fix to use the same class than ConditionNormalizer
                'value' => $data['value'],
            ];
            if ($data['locale']) {
                $condition['locale'] = $data['locale'];
            }
            if ($data['scope']) {
                $condition['scope'] = $data['scope'];
            }
            $content['conditions'][] = $condition;

            $content['actions'] = [];

            $rule->setContent($content);
            $manager = $this->getRuleSaver();
            $manager->save($rule);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product rule setter actions:$/
     */
    public function theFollowingProductRuleSetterActions(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data = array_merge(
                [
                    'locale' => null,
                    'scope'  => null
                ],
                $data
            );

            $rule = $this->getRule($data['rule']);
            $content = $rule->getContent();
            if (!isset($content['actions'])) {
                $content['actions'] = [];
            }

            $code = FieldFilterHelper::getCode($data['field']);
            $attribute = $this->getProductManager()->getAttributeRepository()->findOneBy(['code' => $code]);
            $value = $this->formatActionData($attribute, $data['value']);

            $action = [
                'type'  => 'set_value',
                'field' => $code,
                'value' => $value,
            ];
            if ($data['locale']) {
                $action['locale'] = $data['locale'];
            }
            if ($data['scope']) {
                $action['scope'] = $data['scope'];
            }
            $content['actions'][] = $action;

            $rule->setContent($content);
            $manager = $this->getRuleSaver();
            $manager->save($rule);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product rule copier actions:$/
     */
    public function theFollowingProductRuleCopierActions(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data = array_merge(
                [
                    'locale' => null,
                    'scope'  => null
                ],
                $data
            );

            $rule = $this->getRule($data['rule']);
            $content = $rule->getContent();
            if (!isset($content['actions'])) {
                $content['actions'] = [];
            }
            $action = [
                'type'       => 'copy_value',
                'from_field' => $data['from_field'],
                'to_field'   => $data['to_field'],
            ];
            if ($data['from_locale'] !== null && $data['from_locale'] !== '') {
                $action['from_locale'] = $data['from_locale'];
            }
            if ($data['to_locale'] !== null && $data['to_locale'] !== '') {
                $action['to_locale'] = $data['to_locale'];
            }
            if ($data['from_scope'] !== null && $data['from_scope'] !== '') {
                $action['from_scope'] = $data['from_scope'];
            }
            if ($data['to_scope'] !== null && $data['to_scope'] !== '') {
                $action['to_scope'] = $data['to_scope'];
            }
            $content['actions'][] = $action;

            $rule->setContent($content);
            $manager = $this->getRuleSaver();
            $manager->save($rule);
        }
    }

    /**
     * @param string $code
     *
     * @throws \InvalidArgumentException
     *
     * @return \Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface
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
                assertEquals($data['categories'], $asset->getCategoryCodes());
            }

            if (array_key_exists('tags', $data)) {
                assertEquals($data['tags'], $asset->getTagCodes());
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
        foreach ($table->getHash() as $data) {
            $assetCategory = $this->getAssetCategory($data['code']);
            $this->refresh($assetCategory);

            assertEquals($data['label-en_US'], $assetCategory->getTranslation('en_US')->getLabel());
            assertEquals($data['label-fr_FR'], $assetCategory->getTranslation('fr_FR')->getLabel());
            assertEquals($data['label-de_DE'], $assetCategory->getTranslation('de_DE')->getLabel());
            if (empty($data['parent'])) {
                assertNull($assetCategory->getParent());
            } else {
                assertEquals($data['parent'], $assetCategory->getParent()->getCode());
            }
        }
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
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @return array|bool|int|string
     */
    protected function formatActionData(AttributeInterface $attribute, $data)
    {
        // TODO: replace this dirty fix to use the same class than SetValueActionNormalizer
        switch ($attribute->getAttributeType()) {
            case 'pim_catalog_text':
            case 'pim_catalog_textarea':
            case 'pim_catalog_date':
            case 'pim_catalog_identifier':
            case 'pim_catalog_simpleselect':
            case 'pim_reference_data_simpleselect':
                $value = (string) $data;
                break;
            case 'pim_catalog_number':
                $value = (int) $data;
                break;
            case 'pim_catalog_metric':
                $values = explode(',', $data);
                $value = ['unit' => $values[1], 'data' => $values[0]];
                break;
            case 'pim_catalog_multiselect':
            case 'pim_reference_data_multiselect':
                $value = explode(',', str_replace(' ', '', $data));
                break;
            case 'pim_catalog_price_collection':
                $values = explode(',', $data);
                $value = [['data' => $values[0], 'currency' => $values[1]]];
                break;
            case 'pim_catalog_boolean':
                $value = (bool) $data;
                break;
            case 'pim_catalog_image':
            case 'pim_catalog_file':
                $values = explode(',', $data);
                $value = ['filePath' => $values[1], 'originalFilename' => $values[0]];
                break;
            default:
                throw new \LogicException(sprintf('Unknown attribute type "%s".', $attribute->getAttributeType()));
        }

        return $value;
    }

    /**
     * @return ProductDraftRepositoryInterface
     */
    protected function getProposalRepository()
    {
        return $this->getContainer()->get('pimee_workflow.repository.product_draft');
    }
}
