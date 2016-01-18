<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\RequirementChecker;

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
            $isFieldCollectionOk = true;
            $isFieldOk = $this->checkFieldMapping($configuration->getName(), $configuration->getType());

            if (ConfigurationInterface::TYPE_MULTI === $configuration->getType()) {
                $isFieldCollectionOk = $this->checkCollectionFieldMapping($configuration->getName());
            }
        } catch (\Exception $e) {
            $this->failure = $e->getMessage();

            return false;
        }

        if (!$isFieldOk || !$isFieldCollectionOk) {
            $relationExample = ConfigurationInterface::TYPE_MULTI === $configuration->getType() ? 'options' : 'option';
            $this->failure .= sprintf(
                ' You can take the relation "%s" as example.',
                $relationExample
            );

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldMapping($field)
    {
        $metadata = $this->om->getClassMetadata($this->productValueClass);

        return $metadata->getFieldMapping($field);
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    protected function checkCollectionFieldMapping($field)
    {
        $metadata     = $this->om->getClassMetadata($this->productValueClass);
        $fieldMapping = $metadata->getFieldMapping($field);

        if (!isset($fieldMapping['idsField'])) {
            $this->failure = sprintf(
                'Please configure the "idsField" in your "%s" "%s" relation.',
                $this->productValueClass,
                $field
            );

            return false;
        }

        $collectionField        = $fieldMapping['idsField'];
        $collectionFieldMapping = $metadata->getFieldMapping($collectionField);

        if (!isset($collectionFieldMapping['type']) || 'collection' !== $collectionFieldMapping['type']) {
            $this->failure = sprintf(
                'Please configure the type correctly in your "%s" "%s" relation.',
                $this->productValueClass,
                $collectionField
            );

            return false;
        }

        return true;
    }

    /**
     * @param string $field
     * @param string $referenceDataType
     *
     * @return bool
     */
    protected function checkFieldMapping($field, $referenceDataType)
    {
        $expectedType = ConfigurationInterface::TYPE_MULTI === $referenceDataType ? 'entities' : 'entity';
        $metadata     = $this->om->getClassMetadata($this->productValueClass);
        $fieldMapping = $metadata->getFieldMapping($field);

        if (!isset($fieldMapping['type']) ||
            $fieldMapping['type'] !== $expectedType
            || true !== $fieldMapping['isOwningSide']
        ) {
            $this->failure = sprintf(
                'Please configure the type and the owning side correctly in your "%s" "%s" relation.',
                $this->productValueClass,
                $field
            );

            return false;
        }

        return true;
    }
}
