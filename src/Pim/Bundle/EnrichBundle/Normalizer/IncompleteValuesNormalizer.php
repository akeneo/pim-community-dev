<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes missing values of a product model.
 *
 * @author    Julien Janvier (j.janvier@gmail.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IncompleteValuesNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var \Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory */
    private $requiredValueCollectionFactory;

    /** @var IncompleteValueCollectionFactory */
    private $incompleteValueCollectionFactory;

    /**
     * @param NormalizerInterface              $normalizer
     * @param RequiredValueCollectionFactory   $requiredValueCollectionFactory
     * @param IncompleteValueCollectionFactory $incompleteValueCollectionFactory
     */
    public function __construct(
        NormalizerInterface $normalizer,
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory
    ) {
        $this->normalizer = $normalizer;
        $this->requiredValueCollectionFactory = $requiredValueCollectionFactory;
        $this->incompleteValueCollectionFactory = $incompleteValueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entityWithFamily, $format = null, array $context = []): array
    {
        $family = $entityWithFamily->getFamily();
        if (null === $family) {
            return [];
        }

        if (!$this->isEntityGranted($entityWithFamily)) {
            return [];
        }

        $kindOfCompletenesses = [];

        foreach ($this->getChannelsFromRequirements($family) as $channel) {
            $requiredValuesForChannel = $this->requiredValueCollectionFactory->forChannel($family, $channel);

            $kindOfCompleteness = [
                'channel' => $channel->getCode(),
                'labels' => $this->getChannelLabels($channel),
                'locales' => []
            ];

            foreach ($channel->getLocales() as $locale) {
                $requiredValues = $requiredValuesForChannel->filterByChannelAndLocale($channel, $locale);
                $incompleteValues = $this->incompleteValueCollectionFactory->forChannelAndLocale(
                    $requiredValues,
                    $channel,
                    $locale,
                    $entityWithFamily
                );

                $missingAttributes = [];
                foreach ($incompleteValues->attributes() as $attribute) {
                    if (!$this->isAttributeGranted($attribute)) {
                        continue;
                    }

                    if ($attribute->isLocalizable() && !$this->isLocaleGranted($locale)) {
                        continue;
                    }

                    $missingAttributes[] = [
                        'code' => $attribute->getCode(),
                        'labels' => $this->normalizeAttributeLabels($attribute, $channel->getLocales()->toArray())
                    ];
                }

                $kindOfCompleteness['locales'][$locale->getCode()] = [
                    'missing' => $missingAttributes,
                    'label' => $locale->getName(),
                ];
            }

            $kindOfCompletenesses[] = $kindOfCompleteness;
        }

        return $kindOfCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithFamilyInterface && 'internal_api' === $format;
    }

    /**
     * @param EntityWithFamilyInterface $entityWithFamily
     *
     * @return bool
     */
    protected function isEntityGranted(EntityWithFamilyInterface $entityWithFamily)
    {
        return true;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    protected function isAttributeGranted(AttributeInterface $attribute)
    {
        return true;
    }

    /**
     * @param LocaleInterface $locale
     *
     * @return bool
     */
    protected function isLocaleGranted(LocaleInterface $locale)
    {
        return true;
    }

    /**
     * @param FamilyInterface $family
     *
     * @return Collection
     */
    private function getChannelsFromRequirements(FamilyInterface $family)
    {
        $channels = new ArrayCollection();
        foreach ($family->getAttributeRequirements() as $attributeRequirement) {
            $channel = $attributeRequirement->getChannel();
            if (!$channels->contains($channel)) {
                $channels->add($channel);
            }
        }

        return $channels;
    }

    /**
     * @param ChannelInterface $channel
     *
     * @return string[]
     */
    private function getChannelLabels(ChannelInterface $channel): array
    {
        return array_reduce($channel->getLocales()->toArray(), function ($result, LocaleInterface $locale) use ($channel) {
            $result[$locale->getCode()] = $channel->getTranslation($locale->getCode())->getLabel();

            return $result;
        }, []);
    }

    /**
     * @param AttributeInterface $attribute
     * @param LocaleInterface[]  $locales
     *
     * @return array
     */
    private function normalizeAttributeLabels(AttributeInterface $attribute, array $locales): array
    {
        $result = [];
        foreach ($locales as $locale) {
            $result[$locale->getCode()] = $attribute->getTranslation($locale->getCode())->getLabel();
        }

        return $result;
    }
}
