<?php

namespace Context;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\ProductDraftFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\ChainedStepsExtension\Step;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Context\FixturesContext as BaseFixturesContext;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
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
        'JobProfile'    => 'Akeneo\Tool\Component\Batch\Model\JobInstance',
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

            $draftSource = new DraftSource(
                $data['source'],
                $data['source_label'],
                $data['author'],
                $data['author_label']
            );

            $productDraft = $this->getProductDraftFactory()->createEntityWithValueDraft($product, $draftSource);
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
                $productDraft->setValues(WriteValueCollection::fromCollection($product->getValues()));

                foreach ($changes['values'] as $code => $rawValue) {
                    foreach ($rawValue as $value) {
                        $attribute = $this->getContainer()->get('pim_catalog.repository.attribute')
                                          ->findOneByIdentifier($code);
                        $this->getContainer()->get('pim_catalog.builder.entity_with_values')
                            ->addOrReplaceValue($productDraft, $attribute, $value['locale'], $value['scope'], $value['data']);
                    }
                }
            }

            if ('ready' === $data['status']) {
                $productDraft->markAsReady();
                $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            } else {
                $productDraft->markAsInProgress();
                $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_DRAFT);
            }

            $this->getContainer()->get('pimee_workflow.saver.product_draft')->save($productDraft);
            $this->getContainer()->get('akeneo_elasticsearch.client.product_proposal')->refreshIndex();
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
        $productDraft = $this->getProposalRepository()->findUserEntityWithValuesDraft($product, $username);

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

        $this->assertProductDataValueEquals($value, $productValue, strtolower($attribute));
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) proposals?$/
     */
    public function thereShouldBeProposals($expectedTotal)
    {
        $total = count($this->getProposalRepository()->findAll());

        Assert::assertEquals($expectedTotal, $total);
    }

    /**
     * @param string $identifier
     * @param string $attribute
     * @param string $locale
     * @param string $scope
     *
     * @throws \InvalidArgumentException
     *
     * @return ValueInterface
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
        $registry = $this->getEntityManager();

        $accessManager = $this->getAccessManager($accessType);
        foreach ($table->getHash() as $data) {
            $access = $this->$getterAccessType($data[$accessType]);
            $accessManager->revokeAccess($access);
        }
        $registry->flush();

        foreach ($table->getHash() as $data) {
            $access = $this->$getterAccessType($data[$accessType]);
            $userGroup = $this->getUserGroup($data['user group']);
            $accessLevel = $this->getAccessLevelByAccessTypeAndAction($accessType, $data['access']);

            if ('none' != $accessLevel) {
                $accessManager->grantAccess($access, $userGroup, $accessLevel);
            }
        }
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
     * @return EntityWithValuesDraftRepositoryInterface
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

    /**
     * @return \Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface
     */
    protected function getProductModelRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.product_model_without_permission');
    }
}
