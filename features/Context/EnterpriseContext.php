<?php

namespace Context;

use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
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
     * @Given /^"([^"]*)" has submitted the following proposal for "([^"]*)":$/
     */
    public function hasSubmittedTheFollowingProposalForMySandals($username, $product, TableNode $table)
    {
        $proposal = $this->getProposalFactory()->createProposal(
            $this->getProduct($product),
            $username,
            $table->getRowsHash()
        );

        $manager = $this->getSmartRegistry()->getManagerForClass(get_class($proposal));
        $manager->persist($proposal);
        $manager->flush();
    }

    /**
     * @Given /^the following proposals:$/
     */
    public function theFollowingProposals(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $proposal = $this->getProposalFactory()->createProposal(
                $this->getProduct($data['product']),
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
        foreach ($table->getHash() as $data) {
            $group = $this->getAttributeGroup($data['group']);
            $role  = $this->getRole($data['role']);
            $accessLevel = ($data['access'] === 'edit')
                ? AttributeGroupVoter::EDIT_ATTRIBUTES : AttributeGroupVoter::VIEW_ATTRIBUTES;

            $this->getAttributeGroupAccessManager()->grantAccess($group, $role, $accessLevel);
        }

        $registry = $this->getSmartRegistry()
            ->getManagerForClass('PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess');
        $registry->flush();
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

    protected function getAttributeGroupAccessManager()
    {
        return $this->getContainer()->get('pimee_security.manager.attribute_group_access');
    }

    protected function getProposalFactory()
    {
        return $this->getContainer()->get('pimee_workflow.factory.proposal');
    }
}
