<?php

namespace Context;

use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class EnterpriseContext extends RawMinkContext
{
    public function __construct(array $parameters = [])
    {
        # FeatureContext comes from akeneo/pim-community-dev
        $this->useContext('community', new FeatureContext($parameters));
    }

    /**
     * @BeforeScenario
     */
    public function registerConfigurationDirectory()
    {
        $this
            ->getSubcontext('catalogConfiguration')
            ->addConfigurationDirectory('../../../../../features/Context/catalog');
    }

    /**
     * Fallback all unaccessible method calls to the community context
     *
     * For example, some community sub context might use `$this->getMainContext()`
     * which will be the current class, instead of the community main context
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        $communityCtx = $this->getSubcontext('community');

        if (0 === strpos($method, 'get')) {
            try {
                return call_user_func_array([$communityCtx->getSubcontext('fixtures'), $method], $arguments);
            } catch (\BadMethodCallException $e) {
                return call_user_func_array([$communityCtx, $method], $arguments);
            }
        }

        return call_user_func_array([$communityCtx, $method], $arguments);
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
            $localeCode = isset($data['locale']) ? $data['locale'] : 'en_US';
            $product = $this->getProduct($data['product']);
            $product->setLocale($localeCode);

            $proposition = $this->getPropositionFactory()->createProposition(
                $product,
                $data['author'],
                []
            );
            $proposition->setStatus($data['status'] === 'open' ? Proposition::WAITING : Proposition::APPROVED);
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

        if (count($expectedPropositions) !== count($actualPropositions)) {
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

    protected function getAccessManager($type)
    {
        return $this->getContainer()->get(sprintf('pimee_security.manager.%s_access', str_replace(' ', '_', $type)));
    }

    protected function getPropositionFactory()
    {
        return $this->getContainer()->get('pimee_workflow.factory.proposition');
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
