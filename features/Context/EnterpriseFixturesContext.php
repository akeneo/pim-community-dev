<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Context\FixturesContext as BaseFixturesContext;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use PimEnterprise\Bundle\RuleEngineBundle\Manager\RuleDefinitionManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Behat\Behat\Context\Step;

/**
 * A context for creating entities
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFixturesContext extends BaseFixturesContext
{
    protected $enterpriseEntities = [
        'Published' => 'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct',
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
            $productDraft->setStatus($data['status'] === 'ready' ? ProductDraft::READY : ProductDraft::IN_PROGRESS);
            $manager = $this->getSmartRegistry()->getManagerForClass(get_class($productDraft));
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
     * @return Given[]
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
     * @return Given[]
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
     * @return Given[]
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
     * @Given /^the following category accesses:$/
     */
    public function theFollowingCategoryAccesses(TableNode $table)
    {
        $this->createAccesses($table, 'category');
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
     * @return \Pim\Bundle\CatalogBundle\Model\Product
     *
     * @throws \InvalidArgumentException
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
     * @param TableNode $table
     *
     * @Given /^I should see the following proposals:$/
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
     * @return Given[]
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
     * @return CategoryRepository
     */
    protected function getCategoryRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.category');
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
     * @return string
     * @throws \Exception
     */
    protected function getAccessLevelByAccessTypeAndAction($type, $action)
    {
        if ('none' === $action) {
            return $action;
        }

        if ('attribute group' === $type) {
            return ($action === 'edit') ? Attributes::EDIT_ATTRIBUTES : Attributes::VIEW_ATTRIBUTES;
        }

        if ('category' === $type || 'locale' === $type) {
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
            $rule->setPriority($data['priority']);
            $rule->setType('product');
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
                    'scope' => null
                ],
                $data
            );

            $rule = $this->getRule($data['rule']);
            $content = $rule->getContent();
            $content = json_decode($content, true);
            if (!isset($content['conditions'])) {
                $content['conditions'] = [];
            }
            $condition = [
                'field' => $data['field'],
                'operator' => $data['operator'],
                'value' => $data['value'],
            ];
            if ($data['locale'] !== null) {
                $condition['locale'] = $data['locale'];
            }
            if ($data['scope'] !== null) {
                $condition['scope'] = $data['scope'];
            }
            $content['conditions'][] = $condition;

            $content['actions'] = [];

            $rule->setContent(json_encode($content));
            $manager = $this->getRuleManager();
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
                    'scope' => null
                ],
                $data
            );

            $rule = $this->getRule($data['rule']);
            $content = $rule->getContent();
            $content = json_decode($content, true);
            if (!isset($content['actions'])) {
                $content['actions'] = [];
            }

            $attribute = $this->getProductManager()->getAttributeRepository()->findOneBy(['code' => $data['field']]);
            $attributeType = $attribute->getAttributeType();

            //TODO: replace this dirty fix once rule import is done (and use what's done in it to convert values).
            switch ($attributeType) {
                case 'pim_catalog_text':
                case 'pim_catalog_textarea':
                case 'pim_catalog_date':
                case 'pim_catalog_identifier':
                    $value = (string) $data['value'];
                    break;
                case 'pim_catalog_number':
                    $value = (int) $data['value'];
                    break;
                case 'pim_catalog_metric':
                case 'pim_catalog_multiselect':
                case 'pim_catalog_price_collection':
                    $values = explode(',', $data['value']);
                    $value = [['data' => $values[0], 'currency' => $values[1]]];
                    break;
                case 'pim_catalog_simpleselect':
                    $value = ['code' => $data['value'], 'attribute' => $attribute->getCode()];
                    break;
                case 'pim_catalog_boolean':
                    $value = (bool) $data['value'];
                    break;
                case 'pim_catalog_image':
                case 'pim_catalog_file':
                    $values = explode(',', $data['value']);
                    $value = ['originalFilename' => $values[0], 'filePath' => $values[1]];
                    break;
            }

            $action = [
                'type' => 'set_value',
                'field' => $data['field'],
                'value' => $value,
            ];
            if ($data['locale'] !== null) {
                $action['locale'] = $data['locale'];
            }
            if ($data['scope'] !== null) {
                $action['scope'] = $data['scope'];
            }
            $content['actions'][] = $action;

            $rule->setContent(json_encode($content));
            $manager = $this->getRuleManager();
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
                    'scope' => null
                ],
                $data
            );

            $rule = $this->getRule($data['rule']);
            $content = $rule->getContent();
            $content = json_decode($content, true);
            if (!isset($content['actions'])) {
                $content['actions'] = [];
            }
            $action = [
                'type' => 'copy_value',
                'from_field' => $data['from_field'],
                'to_field' => $data['to_field'],
            ];
            if ($data['to_locale'] !== null) {
                $action['to_locale'] = $data['to_locale'];
            }
            if ($data['to_scope'] !== null) {
                $action['to_scope'] = $data['to_scope'];
            }
            $content['actions'][] = $action;

            $rule->setContent(json_encode($content));
            $manager = $this->getRuleManager();
            $manager->save($rule);
        }
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getRule($code)
    {
        $rule = $this->getRuleRepository()->findOneByCode($code);
        if (!$rule) {
            throw new \InvalidArgumentException(sprintf('Could not find a rule with code "%s"', $code));
        }

        return $rule;
    }

    /**
     * @return RuleDefinitionRepositoryInterface
     */
    protected function getRuleRepository()
    {
        return $this->getContainer()->get('pimee_rule_engine.repository.rule');
    }

    /**
     * @return RuleDefinitionManager
     */
    protected function getRuleManager()
    {
        return $this->getContainer()->get('pimee_rule_engine.manager.rule_definition');
    }
}
