<?php

namespace Context;

use Context\AssertionContext as BaseAssertionContext;

/**
 * Assertion context
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseAssertionContext extends BaseAssertionContext
{
    /**
     * @Then /^the asset basket should contain (.*)$/
     */
    public function theAssetBasketShouldContain($entities)
    {
        foreach ($this->getMainContext()->listToArray($entities) as $entity) {
            $entity = $this->spin(function () use ($entity) {
                return $this->getSession()->getPage()
                    ->find('css', sprintf('.asset-basket li[data-asset="%s"]', $entity));
            });
        }
    }
}
