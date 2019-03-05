<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterInterface;
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
    private const FIELD_ALL_INCOMPLETE = 'all_incomplete';
    private const FIELD_ALL_COMPLETE = 'all_complete';
    private const FIELD_ANCESTORS = 'ancestors';
    private const FIELD_CATEGORIES_OF_ANCESTORS = 'categories_of_ancestors';

    /** @var CompleteFilterInterface */
    private $completenessGridFilterQuery;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /**
     * @todo merge master: remove "= null"
     */
    public function __construct(
        CompleteFilterInterface $completenessGridFilterQuery,
        ChannelRepositoryInterface $channelRepository = null,
        LocaleRepositoryInterface $localeRepository = null
    ) {
        $this->completenessGridFilterQuery = $completenessGridFilterQuery;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        if (!$productModel instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(
                sprintf('Expected type %s, %s given', ProductModelInterface::class, get_class($productModel))
            );
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
        $categoriesOfAncestors = null !== $productModel->getParent() ?
            $productModel->getParent()->getCategoryCodes() : [];
        $data[self::FIELD_CATEGORIES_OF_ANCESTORS] = $categoriesOfAncestors;

        $parentCode = null !== $productModel->getParent() ? $productModel->getParent()->getCode() : null;
        $data[self::FIELD_PARENT] = $parentCode;

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$productModel->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $productModel->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $normalizedData = $this->completenessGridFilterQuery->findCompleteFilterData($productModel);
        $data[self::FIELD_ALL_COMPLETE] = $normalizedData->allComplete();
        $data[self::FIELD_ALL_INCOMPLETE] = $normalizedData->allIncomplete();
        $data[self::FIELD_ANCESTORS] = $this->getAncestors($productModel);

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

        $attributeAsLabel = $productModel->getFamily()->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return [];
        }

        $valuePath = sprintf('%s-text', $attributeAsLabel->getCode());
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

    /**
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getAncestors(ProductModelInterface $productModel): array
    {
        $ancestorsIds = $this->getAncestorsIds($productModel);
        $ancestorsCodes = $this->getAncestorsCodes($productModel);
        $ancestorsLabels = $this->getAncestorsLabels($productModel);

        $ancestors = [
            'ids' => $ancestorsIds,
            'codes' => $ancestorsCodes,
            'labels' => $ancestorsLabels,
        ];

        return $ancestors;
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getAncestorsIds(ProductModelInterface $productModel): array
    {
        $ancestorsIds = [];
        while (null !== $parent = $productModel->getParent()) {
            $ancestorsIds[] = 'product_model_' . $parent->getId();
            $productModel = $parent;
        }

        return $ancestorsIds;
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getAncestorsCodes(ProductModelInterface $productModel): array
    {
        $ancestorsCodes = [];
        while (null !== $parent = $productModel->getParent()) {
            $ancestorsCodes[] = $parent->getCode();
            $productModel = $parent;
        }

        return $ancestorsCodes;
    }

    /**
     * @todo merge master: remove "$hasRepositories".
     *
     * Retrieves ancestors labels for each locales and channels.
     *
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getAncestorsLabels(ProductModelInterface $productModel): array
    {
        $family = $productModel->getFamily();
        if (null === $family) {
            return [];
        }

        $attributeAsLabel = $family->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return [];
        }

        $ancestorsLabels = [];
        $attributeCodeAsLabel = $attributeAsLabel->getCode();
        $hasRepositories = null !== $this->channelRepository && null !== $this->localeRepository;
        switch (true) {
            case $attributeAsLabel->isScopable() && $attributeAsLabel->isLocalizable() && $hasRepositories:
                $ancestorsLabels = $this->getLocalizableAndScopableLabels($productModel, $attributeCodeAsLabel);
                break;

            case $attributeAsLabel->isScopable() && $hasRepositories:
                $ancestorsLabels = $this->getScopableLabels($productModel, $attributeCodeAsLabel);
                break;

            case $attributeAsLabel->isLocalizable() && $hasRepositories:
                $ancestorsLabels = $this->getLocalizableLabels($productModel, $attributeCodeAsLabel);
                break;

            default:
                $value = $productModel->getValue($attributeCodeAsLabel);
                if (null !== $value) {
                    $ancestorsLabels['<all_channels>']['<all_locales>'] = $value->getData();
                }
                break;
        }

        return $ancestorsLabels;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string $attributeCodeAsLabel
     *
     * @return array
     */
    private function getLocalizableAndScopableLabels(
        ProductModelInterface $productModel,
        string $attributeCodeAsLabel
    ): array {
        $ancestorsLabels = [];
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($this->channelRepository->getChannelCodes() as $channelCode) {
            foreach ($localeCodes as $localeCode) {
                $value = $productModel->getValue($attributeCodeAsLabel, $localeCode, $channelCode);
                if (null !== $value) {
                    $ancestorsLabels[$channelCode][$localeCode] = $value->getData();
                }
            }
        }

        return $ancestorsLabels;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string $attributeCodeAsLabel
     *
     * @return array
     */
    private function getScopableLabels(ProductModelInterface $productModel, string $attributeCodeAsLabel): array
    {
        $ancestorsLabels = [];
        foreach ($this->channelRepository->getChannelCodes() as $channelCode) {
            $value = $productModel->getValue($attributeCodeAsLabel, null, $channelCode);
            if (null !== $value) {
                $ancestorsLabels[$channelCode]['<all_locales>'] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string $attributeCodeAsLabel
     *
     * @return array
     */
    private function getLocalizableLabels(ProductModelInterface $productModel, string $attributeCodeAsLabel): array
    {
        $ancestorsLabels = [];
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $localeCode) {
            $value = $productModel->getValue($attributeCodeAsLabel, $localeCode);
            if (null !== $value) {
                $ancestorsLabels['<all_channels>'][$localeCode] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }
}
