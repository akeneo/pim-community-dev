<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\RequirementChecker;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ReferenceDataBundle\RequirementChecker\AbstractProductValueRelationshipChecker;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;

/**
 * Checks if the custom ProductValue has the required relationships.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueRelationshipChecker extends AbstractProductValueRelationshipChecker
{
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
                'Please configure your "%s" relation "%s" correctly. ' .
                'You can take the relation "%s" as example.',
                $this->productValueClass,
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
    protected function getAssociationMapping($referenceData)
    {
        $metadata = $this->om->getClassMetadata($this->productValueClass);

        return $metadata->getAssociationMapping($referenceData);
    }
}
