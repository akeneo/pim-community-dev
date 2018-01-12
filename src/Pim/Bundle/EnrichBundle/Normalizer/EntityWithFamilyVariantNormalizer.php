<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\ProductModel\ImageAsLabel;
use Pim\Component\Catalog\ProductModel\Query\VariantProductRatioInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for entities with family variant, such as VariantProducts and ProductModels.
 * It only returns some properties of these entities, helpful for some display on the front side.
 *
 * To fully normalize a Product or a ProductModel, please use either
 * {@see \Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer} or
 * {@see \Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer}
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EntityWithFamilyVariantNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private $supportedFormat = ['internal_api'];

    /** @var ImageNormalizer */
    private $imageNormalizer;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var NormalizerInterface */
    private $completenessCollectionNormalizer;

    /** @var CompletenessCalculatorInterface */
    private $completenessCalculator;

    /** @var VariantProductRatioInterface */
    private $variantProductRatioQuery;

    /** @var ImageAsLabel */
    private $imageAsLabel;


    /**
     * @param ImageNormalizer                           $imageNormalizer
     * @param LocaleRepositoryInterface                 $localeRepository
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param NormalizerInterface                       $completenessCollectionNormalizer
     * @param CompletenessCalculatorInterface           $completenessCalculator
     * @param VariantProductRatioInterface              $variantProductRatioQuery
     * @param ImageAsLabel                              $imageAsLabel
     */
    public function __construct(
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        NormalizerInterface $completenessCollectionNormalizer,
        CompletenessCalculatorInterface $completenessCalculator,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel
    ) {
        $this->imageNormalizer                  = $imageNormalizer;
        $this->localeRepository                 = $localeRepository;
        $this->attributesProvider               = $attributesProvider;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
        $this->completenessCalculator           = $completenessCalculator;
        $this->variantProductRatioQuery         = $variantProductRatioQuery;
        $this->imageAsLabel                     = $imageAsLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = []): array
    {
        if (!$entity instanceof ProductModelInterface && !$entity instanceof ProductInterface) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" or "%s" expected, "%s" received',
                ProductModelInterface::class,
                ProductInterface::class,
                get_class($entity)
            ));
        }

        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();

        $labels = [];
        foreach ($localeCodes as $localeCode) {
            $labels[$localeCode] = $entity->getLabel($localeCode);
        }

        $identifier = $entity instanceof ProductModelInterface ? $entity->getCode() : $entity->getIdentifier();

        if ($entity instanceof ProductModelInterface) {
            $image = $this->imageAsLabel->value($entity);
        } else {
            $image = $entity->getImage();
        }

        return [
            'id'                 => $entity->getId(),
            'identifier'         => $identifier,
            'axes_values_labels' => $this->getAxesValuesLabelsForLocales($entity, $localeCodes),
            'labels'             => $labels,
            'order'              => $this->getOrder($entity),
            'image'              => $this->normalizeImage($image, $context),
            'model_type'         => $entity instanceof ProductModelInterface ? 'product_model' : 'product',
            'completeness'       => $this->getCompletenessDependingOnEntity($entity)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EntityWithFamilyVariantInterface && in_array($format, $this->supportedFormat);
    }

    /**
     * @param ValueInterface $data
     * @param array          $context
     *
     * @return array|null
     */
    private function normalizeImage(?ValueInterface $data, array $context = []): ?array
    {
        return $this->imageNormalizer->normalize($data);
    }

    /**
     * Get axes values labels for the $entity on the given $localeCodes for all axes values.
     * For example:
     * [
     *      'fr_FR' => 'Jaune, XL',
     *      'en_US' => 'Yellow, XL',
     * ]
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @param array                            $localeCodes
     *
     * @throws \LogicException
     *
     * @return array
     */
    private function getAxesValuesLabelsForLocales(EntityWithFamilyVariantInterface $entity, array $localeCodes): array
    {
        $axesValuesLabels = [];

        foreach ($localeCodes as $localeCode) {
            $valuesForLocale = [];

            foreach ($this->attributesProvider->getAxes($entity) as $axisAttribute) {
                $value = $entity->getValue($axisAttribute->getCode());

                switch ($axisAttribute->getType()) {
                    case AttributeTypes::OPTION_SIMPLE_SELECT:
                        $option = $value->getData();
                        $option->setLocale($localeCode);
                        $label = $option->getTranslation()->getLabel();
                        $valuesForLocale[] = empty($label) ? '[' . $option->getCode() . ']' : $label;

                        break;
                    case AttributeTypes::METRIC:
                        $valuesForLocale[] = sprintf(
                            '%s %s',
                            $value->getAmount(),
                            $value->getUnit()
                        );

                        break;
                    default:
                        $valuesForLocale[] = (string) $value;

                        break;
                }
            }

            $axesValuesLabels[$localeCode] = implode(', ', $valuesForLocale);
        }

        return $axesValuesLabels;
    }

    /**
     * Get completeness of the given $entity, whether it's a ProductModel or a VariantProduct.
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return array
     */
    private function getCompletenessDependingOnEntity(EntityWithFamilyVariantInterface $entity): array
    {
        if ($entity instanceof ProductModelInterface) {
            return $this->variantProductRatioQuery->findComplete($entity)->values();
        }

        if ($entity instanceof ProductInterface && $entity->isVariant()) {
            $completenessCollection = $entity->getCompletenesses();
            if ($completenessCollection->isEmpty()) {
                $newCompletenesses = $this->completenessCalculator->calculate($entity);
                foreach ($newCompletenesses as $completeness) {
                    $completenessCollection->add($completeness);
                }
            }

            return $this->completenessCollectionNormalizer->normalize($completenessCollection, 'internal_api');
        }

        return [];
    }

    /**
     * Generate an array for the given $entity to represent its order among all its axes values.
     *
     * For example, if its axes values are "Blue, 10 CENTIMETER" and Blue is an option with a sort order equals to 4,
     * it will return [4, blue, 10 CENTIMETER].
     *
     * It allows to sort on front-end to respect sort orders of attribute options.
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return array
     */
    private function getOrder(EntityWithFamilyVariantInterface $entity): array
    {
        $orderArray = [];

        foreach ($this->attributesProvider->getAxes($entity) as $axisAttribute) {
            $value = $entity->getValue($axisAttribute->getCode());

            if (AttributeTypes::OPTION_SIMPLE_SELECT === $axisAttribute->getType()) {
                $option = $value->getData();
                $orderArray[] = $option->getSortOrder();
                $orderArray[] = $option->getCode();
            } else {
                $orderArray[] = (string) $value;
            }
        }

        return $orderArray;
    }
}
