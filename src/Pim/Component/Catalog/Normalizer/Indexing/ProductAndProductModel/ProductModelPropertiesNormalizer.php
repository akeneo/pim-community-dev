<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;

use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Pim\Component\Catalog\ProductAndProductModel\Query\CompleteFilterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Transform the properties of a product model object (fields and product values)
 * to the indexing_product_and_model format.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelPropertiesNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_ID = 'id';
    private const FIELD_PARENT = 'parent';
    private const FIELD_AT_LEAST_COMPLETE = 'at_least_complete';
    private const FIELD_AT_LEAST_INCOMPLETE = 'at_least_incomplete';

    /** @var CompleteFilterInterface */
    private $completenessGridFilterQuery;

    public function __construct(CompleteFilterInterface $completenessGridFilterQuery)
    {
        $this->completenessGridFilterQuery = $completenessGridFilterQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = 'product_model_' . (string) $productModel->getId();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = $productModel->getCode();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->serializer->normalize(
            $productModel->getCreated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_UPDATED] = $this->serializer->normalize(
            $productModel->getUpdated(),
            $format
        );

        $family = null;
        $familyVariant = null;
        if (null !== $productModel->getFamilyVariant()) {
            $family = $this->serializer->normalize(
                $productModel->getFamilyVariant()->getFamily(),
                $format
            );
            $familyVariant = $productModel->getFamilyVariant()->getCode();
        }
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = $family;
        $data[self::FIELD_FAMILY_VARIANT] = $familyVariant;

        $data[StandardPropertiesNormalizer::FIELD_CATEGORIES] = $productModel->getCategoryCodes();

        $parentCode = null !== $productModel->getParent() ? $productModel->getParent()->getCode() : null;
        $data[self::FIELD_PARENT] = $parentCode;

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$productModel->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $productModel->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $normalizedData = $this->completenessGridFilterQuery->findCompleteFilterData($productModel);
        $data[self::FIELD_AT_LEAST_COMPLETE] = $normalizedData->atLeastComplete();
        $data[self::FIELD_AT_LEAST_INCOMPLETE] = $normalizedData->atLeastIncomplete();


        $data[StandardPropertiesNormalizer::FIELD_LABEL] = $this->getLabel(
            $data[StandardPropertiesNormalizer::FIELD_VALUES],
            $productModel
        );

        return $data;
    }

    /**
     * Get label of the given product model
     *
     * @param array                 $values
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getLabel(array $values, ProductModelInterface $productModel): array
    {
        if (null === $productModel->getFamily()) {
            return [];
        }
        $valuePath = sprintf('%s-text', $productModel->getFamily()->getAttributeAsLabel()->getCode());
        if (!isset($values[$valuePath])) {
            return [];
        }

        return $values[$valuePath];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface
            && ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }
}
