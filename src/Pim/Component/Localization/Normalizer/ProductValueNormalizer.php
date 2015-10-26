<?php

namespace Pim\Component\Localization\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize product values with a localized format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['csv', 'flat'];

    /** @var NormalizerInterface */
    protected $valuesNormalizer;

    /** @var LocalizerInterface */
    protected $numberLocalizer;

    /** @var LocalizerInterface */
    protected $dateLocalizer;

    /**
     * @param NormalizerInterface $valuesNormalizer
     * @param LocalizerInterface  $numberLocalizer
     * @param LocalizerInterface  $dateLocalizer
     */
    public function __construct(
        NormalizerInterface $valuesNormalizer,
        LocalizerInterface $numberLocalizer,
        LocalizerInterface $dateLocalizer
    ) {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->numberLocalizer  = $numberLocalizer;
        $this->dateLocalizer    = $dateLocalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        $result = $this->valuesNormalizer->normalize($entity, $format, $context);

        $localizer = $this->getLocalizer($entity);
        if (null !== $localizer) {
            foreach ($result as $field => $data) {
                $result[$field] = $localizer->convertDefaultToLocalized($data, $context);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns a localizer from the entity type. If no localizer can be found, returns null.
     *
     * @param mixed $entity
     *
     * @return LocalizerInterface|null
     */
    protected function getLocalizer($entity)
    {
        $type = $entity->getAttribute()->getAttributeType();
        $localizers = [$this->numberLocalizer, $this->dateLocalizer];

        foreach ($localizers as $localizer) {
            if ($localizer->supports($type)) {
                return $localizer;
            }
        }

        return null;
    }
}
