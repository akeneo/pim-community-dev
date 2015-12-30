<?php

namespace PimEnterprise\Behat\Context;

use Behat\Behat\Context\Step;
use Pim\Behat\Context\HookContext as BaseHookContext;

class HookContext extends BaseHookContext
{
    /**
     * @BeforeScenario
     */
    public function registerConfigurationDirectory()
    {
        $this->getMainContext()->getSubcontext('catalogConfiguration')
            ->addConfigurationDirectory(__DIR__.'/../../../Context/catalog');
    }

    /**
     * @BeforeScenario
     */
    public function resetImageFile()
    {
        $this->imageFile = null;
    }
}
