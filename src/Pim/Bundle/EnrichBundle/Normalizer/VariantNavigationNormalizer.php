<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for the variant navigation data of the given entity.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantNavigationNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private $supportedFormat = ['internal_api'];

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var EntityWithFamilyVariantNormalizer */
    private $entityWithFamilyVariantNormalizer;

    /**
     * @param LocaleRepositoryInterface         $localeRepository
     * @param EntityWithFamilyVariantNormalizer $entityWithFamilyVariantNormalizer
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantNormalizer $entityWithFamilyVariantNormalizer
    ) {
        $this->localeRepository = $localeRepository;
        $this->entityWithFamilyVariantNormalizer = $entityWithFamilyVariantNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        if (!$entity instanceof ProductModelInterface && !$entity instanceof VariantProductInterface) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" or "%s" expected, "%s" received',
                ProductModelInterface::class,
                VariantProductInterface::class,
                get_class($entity)
            ));
        }

        $navigationData = [
            'root'      => null,
            'level_one' => [
                'axes'     => [],
                'selected' => null,
            ],
            'level_two' => [
                'axes'     => [],
                'selected' => null,
            ],
        ];

        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($entity->getFamilyVariant()->getVariantAttributeSets() as $attributeSet) {
            $level = (1 === $attributeSet->getLevel()) ? 'level_one' : 'level_two';
            foreach ($attributeSet->getAxes() as $axis) {
                foreach ($localeCodes as $localeCode) {
                    $axis->setLocale($localeCode);
                    $navigationData[$level]['axes'][$localeCode] = $axis->getLabel();
                }
            }
        }

        $parent = $entity->getParent();
        if (null === $parent) {
            $navigationData['root'] = $this->entityWithFamilyVariantNormalizer
                ->normalize($entity, $format, $context);

            return $navigationData;
        }

        $grandParent = $parent->getParent();
        if (null === $grandParent) {
            $navigationData['root'] = $this->entityWithFamilyVariantNormalizer
                ->normalize($parent, $format, $context);
            $navigationData['level_one']['selected'] = $this->entityWithFamilyVariantNormalizer
                ->normalize($entity, $format, $context);

            return $navigationData;
        }

        $navigationData['root'] = $this->entityWithFamilyVariantNormalizer
            ->normalize($grandParent, $format, $context);
        $navigationData['level_one']['selected'] = $this->entityWithFamilyVariantNormalizer
            ->normalize($parent, $format, $context);
        $navigationData['level_two']['selected'] = $this->entityWithFamilyVariantNormalizer
            ->normalize($entity, $format, $context);

        return $navigationData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithFamilyVariantInterface && in_array($format, $this->supportedFormat);
    }
}
