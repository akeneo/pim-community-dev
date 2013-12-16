<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Context for commands
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandContext extends RawMinkContext
{
    /**
     * @Given /^I launched the completeness calculator$/
     */
    public function iLaunchedTheCompletenessCalculator()
    {
        $this
            ->getContainer()
            ->get('pim_catalog.manager.completeness')
            ->generateAllCompletenesses();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }
}
