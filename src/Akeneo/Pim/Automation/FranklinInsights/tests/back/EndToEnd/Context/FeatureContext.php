<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\EndToEnd\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Context\EnterpriseAssertionContext;
use Context\EnterpriseCatalogConfigurationContext;
use Context\EnterpriseFeatureContext;
use Context\EnterpriseFixturesContext;
use PimEnterprise\Behat\Context\HookContext;
use PimEnterprise\Behat\Context\NavigationContext;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class FeatureContext extends EnterpriseFeatureContext
{
    /**
     * @param BeforeScenarioScope $scope
     *
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $env = $scope->getEnvironment();

        $this->contexts['fixtures'] = $env->getContext(EnterpriseFixturesContext::class);
        $this->contexts['catalogConfiguration'] = $env->getContext(EnterpriseCatalogConfigurationContext::class);
        $this->contexts['navigation'] = $env->getContext(NavigationContext::class);
        $this->contexts['hook'] = $env->getContext(HookContext::class);
        $this->contexts['assertions'] = $env->getContext(EnterpriseAssertionContext::class);
    }
}
