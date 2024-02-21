<?php

namespace Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker;

use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;

/**
 * Checks if the ReferenceData implements the interface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataInterfaceChecker implements CheckerInterface
{
    /** @var string */
    protected $interface;

    /** @var string */
    protected $model;

    /** @var string */
    protected $failure;

    public function __construct($referenceDataInterface)
    {
        $this->interface = $referenceDataInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function check(ReferenceDataConfigurationInterface $configuration)
    {
        $this->model = $configuration->getClass();
        $reflection = new \ReflectionClass($this->model);

        if (!$reflection->implementsInterface($this->interface)) {
            $this->failure = sprintf(
                'Please implement "%s" for your Reference Data model "%s".',
                $this->interface,
                $this->model
            );

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return sprintf('Reference data models must implement "%s".', $this->interface);
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
        return false;
    }
}
