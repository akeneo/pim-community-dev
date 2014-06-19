<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Context\FixturesContext as BaseFixturesContext;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PropositionFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * A context for creating entities
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFixturesContext extends BaseFixturesContext
{
    /**
     * {@inheritdoc}
     */
    public function createProduct($data)
    {
        $product = parent::createProduct($data);

        // add the first root category to all created products so that they can be edited
        $categories = $this->getCategoryRepository()->findAll();
        foreach ($categories as $category) {
            if ($category->isRoot()) {
                $product->addCategory($category);
                break;
            }
        }

        $this->getProductManager()->getObjectManager()->flush();

        return $product;
    }


    /**
     * @Given /^role "([^"]*)" has the permission to (view|edit) the attribute group "([^"]*)"$/
     */
    public function roleHasThePermissionToEditTheAttributeGroup($role, $accessLevel, $attributeGroup)
    {
        $role = $this->getRole($role);
        $attributeGroup = $this->getAttributeGroup($attributeGroup);

        $this
            ->getAccessManager('attribute_group')
            ->setAccess($attributeGroup, [$role], $accessLevel === 'edit' ? [$role] : []);
    }

    /**
     * @Given /^the following propositions:$/
     */
    public function theFollowingPropositions(TableNode $table)
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

            $proposition = $this->getPropositionFactory()->createProposition(
                $product,
                $data['author'],
                []
            );
            $proposition->setStatus($data['status'] === 'ready' ? Proposition::READY : Proposition::IN_PROGRESS);
            $manager = $this->getSmartRegistry()->getManagerForClass(get_class($proposition));
            $manager->persist($proposition);
        }
        $manager->flush();
    }

    /**
     * @Given /^the following attribute group accesses:$/
     */
    public function theFollowingAttributeGroupAccesses(TableNode $table)
    {
        $this->createAccesses($table, 'attribute group');
    }

    /**
     * @Given /^the following category accesses:$/
     */
    public function theFollowingAccesses(TableNode $table)
    {
        $this->createAccesses($table, 'category');
    }

    /**
     * @Given /^I should the following proposition:$/
     */
    public function iShouldTheFollowingProposition(TableNode $table)
    {
        $expectedPropositions = $table->getHash();
        $actualPropositions = $this->getSession()->getPage()->findAll('css', '#propositions-widget tbody tr');

        $expectedCount = count($expectedPropositions);
        $actualCount   = count($actualPropositions);
        if ($expectedCount !== $actualCount) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting %d propositions, actually saw %d',
                    $expectedCount,
                    $actualCount
                )
            );
        }

        foreach ($expectedPropositions as $key => $proposition) {
            $cells = $actualPropositions[$key]->findAll('css', 'td');
            if ($cells[1]->getText() !== $proposition['author']) {
                throw $this->createExpectationException(
                    sprintf(
                        'Proposition #%d author is expected to be "%s", actually is "%s"',
                        $key + 1,
                        $proposition['author'],
                        $cells[1]->getText()
                    )
                );
            }

            if ($cells[2]->getText() !== $proposition['product']) {
                throw $this->createExpectationException(
                    sprintf(
                        'Proposition #%d product is expected to be "%s", actually is "%s"',
                        $key + 1,
                        $proposition['product'],
                        $cells[2]->getText()
                    )
                );
            }
        }
    }

    /**
     * @param $type
     *
     * @return AttributeGroupAccessManager|CategoryAccessManager
     */
    protected function getAccessManager($type)
    {
        return $this->getContainer()->get(sprintf('pimee_security.manager.%s_access', str_replace(' ', '_', $type)));
    }

    /**
     * @return PropositionFactory
     */
    protected function getPropositionFactory()
    {
        return $this->getContainer()->get('pimee_workflow.factory.proposition');
    }

    /**
     * @return CategoryRepository
     */
    protected function getCategoryRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.category');
    }

    /**
     * Get the access level according to the access type and action (view or edit)
     *
     * @param $type
     * @param $action
     *
     * @return string
     * @throws \Exception
     */
    protected function getAccessLevelByAccessTypeAndAction($type, $action)
    {
        if ('attribute group' === $type) {
            return ($action === 'edit') ? AttributeGroupVoter::EDIT_ATTRIBUTES : AttributeGroupVoter::VIEW_ATTRIBUTES;
        }

        if ('category' === $type) {
            return ($action === 'edit') ? CategoryVoter::EDIT_PRODUCTS : CategoryVoter::VIEW_PRODUCTS;
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
            $role  = $this->getRole($data['role']);
            $accessLevel = $this->getAccessLevelByAccessTypeAndAction($accessType, $data['access']);

            $this->getAccessManager($accessType)->grantAccess($access, $role, $accessLevel);
        }

        $registry = $this->getSmartRegistry()
            ->getManagerForClass(sprintf('PimEnterprise\Bundle\SecurityBundle\Entity\%sAccess', $accessClass));
        $registry->flush();
    }
}
