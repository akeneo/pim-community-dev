<?php

namespace Pim\Bundle\ReferenceDataBundle\RequirementChecker;

use Pim\Component\ReferenceData\MethodNameGuesser;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;

/**
 * Checks if the custom ProductValue has the required accessors.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueAccessorsChecker implements CheckerInterface
{
    /** @var string */
    protected $productValueClass;

    /** @var array */
    protected $missingAccessors;

    /** @var string */
    protected $failure;

    /**
     * @param string $productValueClass
     */
    public function __construct($productValueClass)
    {
        $this->productValueClass = $productValueClass;
        $this->missingAccessors = [];
    }

    /**
     * {@inheritdoc}
     */
    public function check(ConfigurationInterface $configuration)
    {
        $reflection  = new \ReflectionClass($this->productValueClass);

        foreach ($this->getRequiredAccessorForSimpleReferenceData($configuration->getName()) as $accessor) {
            if (!$reflection->hasMethod($accessor)) {
                $this->missingAccessors[] = $accessor;
            }
        }

        if (ConfigurationInterface::TYPE_MULTI === $configuration->getType()) {
            foreach ($this->getRequiredAccessorForMultipleReferenceData($configuration->getName()) as $accessor) {
                if (!$reflection->hasMethod($accessor)) {
                    $this->missingAccessors[] = $accessor;
                }
            }
        }

        if (0 !== count($this->missingAccessors)) {
            $this->failure = sprintf(
                'Please implement the accessors "%s" for "%s".',
                implode(', ', $this->missingAccessors),
                $this->productValueClass
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
        return 'Product Value model must implement the required accessors.';
    }

    /**
     * {@inheritdoc}
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * @param string $referenceData
     *
     * @return array
     */
    protected function getRequiredAccessorForSimpleReferenceData($referenceData)
    {
        $accessors   = [];
        $accessors[] = MethodNameGuesser::guess('get', $referenceData);
        $accessors[] = MethodNameGuesser::guess('set', $referenceData);

        return $accessors;
    }

    /**
     * @param string $referenceData
     *
     * @return array
     */
    protected function getRequiredAccessorForMultipleReferenceData($referenceData)
    {
        $accessors   = [];
        $accessors[] = MethodNameGuesser::guess('add', $referenceData, true);
        $accessors[] = MethodNameGuesser::guess('remove', $referenceData, true);

        return $accessors;
    }

    /**
     * {@inheritdoc}
     */
    public function isBlockingOnFailure()
    {
        return false;
    }
}
