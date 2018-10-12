<?php

namespace Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker;

use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Checks if the ReferenceData has a unique code constraint.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractReferenceDataUniqueCodeChecker implements CheckerInterface
{
    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $failure;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * {@inheritdoc}
     */
    public function check(ReferenceDataConfigurationInterface $configuration)
    {
        try {
            $mapping = $this->getCodeFieldMapping($configuration->getClass());
        } catch (\Exception $e) {
            $this->failure = $e->getMessage();

            return false;
        }

        if (!isset($mapping['unique']) || true !== $mapping['unique']) {
            $this->failure = 'Please configure a "code" column with a unique constraint ' .
                             'in your Reference Data mapping.';

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Reference Data mapping must have a unique "code" field.';
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

    /**
     * Get the Doctrine mapping of the "code" field.
     * Depending on Doctrine's storage, this method has to be changed.
     *
     * @param string $referenceDataClass
     *
     * @return array
     */
    abstract protected function getCodeFieldMapping($referenceDataClass);
}
