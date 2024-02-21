<?php

namespace Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker;

use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;

/**
 * Interface that determines a reference data configuration is valid or not.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CheckerInterface
{
    /**
     * Checks the reference data configuration
     *
     * @param ReferenceDataConfigurationInterface $configuration
     *
     * @return bool
     */
    public function check(ReferenceDataConfigurationInterface $configuration);

    /**
     * Gets the description of the check that is performed
     *
     * @return string
     */
    public function getDescription();

    /**
     * In case the configuration is invalid, gets the failure message.
     *
     * @return string
     */
    public function getFailure();

    /**
     * In case the configuration is invalid, tells if other checks should be performed or not.
     *
     * @return bool
     */
    public function isBlockingOnFailure();
}
