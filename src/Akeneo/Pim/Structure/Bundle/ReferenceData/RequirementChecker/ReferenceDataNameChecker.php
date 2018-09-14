<?php

namespace Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker;

use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;

/**
 * Checks if the ReferenceData has a valid name.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNameChecker implements CheckerInterface
{
    /** @var string */
    protected $failure;

    /**
     * {@inheritdoc}
     */
    public function check(ReferenceDataConfigurationInterface $configuration)
    {
        $name = $configuration->getName();

        if (1 === preg_match("/[^A-Za-z]/", $name)) {
            $this->failure = sprintf('Please use a proper name instead of "%s" for your Reference Data.', $name);

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return sprintf('Reference data names must use only letters and be camel-cased.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * {@inheritdoc}
     */
    public function isBlockingOnFailure()
    {
        return true;
    }
}
