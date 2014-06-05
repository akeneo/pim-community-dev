<?php

namespace Context;

use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;

class EnterpriseContext extends RawMinkContext
{
    public function __construct(array $parameters = [])
    {
        # FeatureContext comes from akeneo/pim-community-dev
        $this->useContext('community', new FeatureContext($parameters));
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
     * @Given /^role "([^"]*)" has the right to (view|edit) the attribute group "([^"]*)"$/
     */
    public function roleHasTheRightToEditTheAttributeGroup($role, $accessLevel, $attributeGroup)
    {
        $role = $this->getRole($role);
        $attributeGroup = $this->getAttributeGroup($attributeGroup);

        $this
            ->getAttributeGroupAccessManager()
            ->setAccess($attributeGroup, [$role], $accessLevel === 'edit' ? [$role] : []);
    }

    /**
     * @Given /^the following proposals:$/
     */
    public function theFollowingProposals(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $localeCode = isset($data['locale']) ? $data['locale'] : 'en_US';
            $product = $this->getProduct($data['product']);
            $product->setLocale($localeCode);

            $proposal = $this->getProposalFactory()->createProposal(
                $product,
                $data['author'],
                []
            );
            $proposal->setStatus($data['status'] === 'open' ? Proposal::WAITING : Proposal::APPROVED);
            $manager = $this->getSmartRegistry()->getManagerForClass(get_class($proposal));
            $manager->persist($proposal);
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
     * @Given /^I should the following proposal:$/
     */
    public function iShouldTheFollowingProposal(TableNode $table)
    {
        $expectedProposals = $table->getHash();
        $actualProposals = $this->getSession()->getPage()->findAll('css', '#proposals-widget tbody tr');

        if (count($expectedProposals) !== count($actualProposals)) {
            throw $this->createExpectationException(
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
                throw $this->createExpectationException(
                    sprintf(
                        'Proposal #%d author is expected to be "%s", actually is "%s"',
                        $key + 1,
                        $proposal['author'],
                        $cells[1]->getText()
                    )
                );
            }

            if ($cells[2]->getText() !== $proposal['product']) {
                throw $this->createExpectationException(
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

    protected function getAccessManager($type)
    {
        return $this->getContainer()->get(sprintf('pimee_security.manager.%s_access', str_replace(' ', '_', $type)));
    }

    protected function getProposalFactory()
    {
        return $this->getContainer()->get('pimee_workflow.factory.proposal');
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
        $accessClass = str_replace(' ','', ucwords($accessType));
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
