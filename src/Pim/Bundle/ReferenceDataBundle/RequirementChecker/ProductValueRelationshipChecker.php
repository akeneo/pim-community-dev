<?php

namespace Pim\Bundle\ReferenceDataBundle\RequirementChecker;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;

/**
 * Checks if the custom ProductValue has the required relationships.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueRelationshipChecker implements CheckerInterface
{
    /** @var string */
    protected $productValueClass;

    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $failure;

    /**
     * @param ObjectManager $om
     * @param string        $productValueClass
     */
    public function __construct(ObjectManager $om, $productValueClass)
    {
        $this->om                = $om;
        $this->productValueClass = $productValueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function check(ConfigurationInterface $configuration)
    {
        try {
            $mapping = $this->getAssociationMapping($configuration->getName());
        } catch (\Exception $e) {
            $this->failure = $e->getMessage();

            return false;
        }

        if (ConfigurationInterface::TYPE_MULTI === $configuration->getType()) {
            $expectedType    = ClassMetadataInfo::MANY_TO_MANY;
            $relationExample = 'options';
        } else {
            $expectedType    = ClassMetadataInfo::MANY_TO_ONE;
            $relationExample = 'option';
        }

        if ($mapping['type'] !== $expectedType || true !== $mapping['isOwningSide']) {
            $this->failure = sprintf(
                'Please configure your Product Value relation "%s" correctly. ' .
                'You can take the relation "%s" as example.',
                $configuration->getName(),
                $relationExample
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
        return 'Relation between the Product Value and the Reference Data must be configured.';
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
     * Get the Doctrine mapping of the relationship between Product Value and the Reference Data.
     *
     * @param string $referenceData
     *
     * @return array
     */
    protected function getAssociationMapping($referenceData)
    {
        $metadata = $this->om->getClassMetadata($this->productValueClass);

        // TODO: this is pure ORM stuff, maybe that should go in a Doctrine/ directory
        return $metadata->getAssociationMapping($referenceData);
    }
}
