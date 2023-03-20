<?php

namespace Context;

use Pim\Behat\Context\PimContext;

/**
 * Context for assertions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureFlagContext extends PimContext
{
    /**
     * @param string $featureFlag
     *
     * @Given /^feature flag "([^"]*)" is activated$/
     */
    public function activateFeatureFlag($featureFlag)
    {
        $this->getService('feature_flags')->enable($featureFlag);
    }
}
