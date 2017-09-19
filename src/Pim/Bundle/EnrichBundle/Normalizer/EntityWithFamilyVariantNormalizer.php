<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
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

    /** @var FileNormalizer */
    private $fileNormalizer;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var NormalizerInterface */
    private $completenessCollectionNormalizer;

    /** @var CompletenessCalculatorInterface */
    private $completenessCalculator;

    /**
     * @param FileNormalizer                            $fileNormalizer
     * @param LocaleRepositoryInterface                 $localeRepository
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param NormalizerInterface                       $completenessCollectionNormalizer
     * @param CompletenessCalculatorInterface           $completenessCalculator
     */
    public function __construct(
        FileNormalizer $fileNormalizer,
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        NormalizerInterface $completenessCollectionNormalizer,
        CompletenessCalculatorInterface $completenessCalculator
    ) {
        $this->fileNormalizer = $fileNormalizer;
        $this->localeRepository = $localeRepository;
        $this->attributesProvider = $attributesProvider;
        $this->completenessCollectionNormalizer = $completenessCollectionNormalizer;
        $this->completenessCalculator = $completenessCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = []): array
    {
        if (!$entity instanceof ProductModelInterface && !$entity instanceof VariantProductInterface) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" or "%s" expected, "%s" received',
                ProductModelInterface::class,
                VariantProductInterface::class,
                get_class($entity)
            ));
        }

        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();

        $labels = [];
        foreach ($localeCodes as $localeCode) {
            $labels[$localeCode] = $entity->getLabel($localeCode);
        }

        $identifier = $entity instanceof ProductModelInterface ? $entity->getCode() : $entity->getIdentifier();

        return [
            'id'                 => $entity->getId(),
            'identifier'         => $identifier,
            'axes_values_labels' => $this->getAxesValuesLabelsForLocales($entity, $localeCodes),
            'labels'             => $labels,
            'image'              => $this->normalizeImage($entity->getImage(), $format, $context),
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
     * @param string         $format
     * @param array          $context
     *
     * @return array|null
     */
    private function normalizeImage(?ValueInterface $data, $format, $context = []): ?array
    {
        if (null === $data || null === $data->getData()) {
            return null;
        }

        return $this->fileNormalizer->normalize($data->getData(), $format, $context);
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

                if (AttributeTypes::OPTION_SIMPLE_SELECT === $axisAttribute->getType()) {
                    $option = $value->getData();
                    $option->setLocale($localeCode);
                    $valuesForLocale[] = $option->getTranslation()->getLabel();
                } else {
                    $valuesForLocale[] = (string) $value;
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
            // TODO: replace this placeholder by the real values, with PIM-6560
            return [];
        }

        if ($entity instanceof VariantProductInterface) {
            $completenessCollection = $entity->getCompletenesses();
            if ($completenessCollection->isEmpty()) {
                $newCompletenesses = $this->completenessCalculator->calculate($entity);
                foreach ($newCompletenesses as $completeness) {
                    $completenessCollection->add($completeness);
                }
            }

            return $this->completenessCollectionNormalizer->normalize($completenessCollection, 'internal_api');
        }
    }
}
