<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Step;

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
     * @Given /^role "([^"]*)" has the right to edit the attribute group "([^"]*)"$/
     */
    public function roleHasTheRightToEditTheAttributeGroup($role, $attributeGroup)
    {
        $role = $this->getRole($role);
        $attributeGroup = $this->getAttributeGroup($attributeGroup);

        $this
            ->getAttributeGroupAccessManager()
            ->setAccess($attributeGroup, [$role], [$role]);
    }

    protected function getAttributeGroupAccessManager()
    {
        return $this->getContainer()->get('pimee_security.manager.attribute_group_access');
    }
}
