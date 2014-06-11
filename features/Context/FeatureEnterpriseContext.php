<?php

namespace Context;

use Behat\Behat\Context\Step;

/**
 * A context for creating entities
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 */
class FeatureEnterpriseContext extends FeatureContext
{
    public function __construct(array $parameters = [])
    {
        $this->useContext('fixtures', new FixturesEnterpriseContext());
        $this->useContext('catalogConfiguration', new CatalogConfigurationContext());
        $this->useContext('webUser', new WebUser($parameters['window_width'], $parameters['window_height']));
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new DataGridContext());
        $this->useContext('command', new CommandContext());
        $this->useContext('navigation', new NavigationContext());
        $this->useContext('transformations', new TransformationContext());
        $this->useContext('assertions', new AssertionContext());
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
}
