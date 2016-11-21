<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionNormalizer as BaseAttributeOptionNormalizer;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class StructuredAttributeOptionNormalizer extends BaseAttributeOptionNormalizer
{
    /** @var array */
    protected $supportedFormat = ['json'];

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param ObjectFilterInterface $objectFilter
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        SimpleFactoryInterface $attributeOptionValueFactory,
        ObjectFilterInterface $objectFilter
    ) {
        parent::__construct($localeRepository, $attributeOptionValueFactory);

        $this->objectFilter = $objectFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $optionsValues = $context['onlyActivatedLocales'] ?
            $this->ensureEmptyOptionValues($object->getOptionValues()) :
            $object->getOptionValues();

        $normalizedLabels = $this->normalizeOptionsValues($optionsValues);

        return [
            'code'   => $object->getCode(),
            'labels' => $normalizedLabels,
        ];
    }

    /**
     * Normalize and return given options values
     *
     * @param Collection $optionsValues
     *
     * @return array
     */
    protected function normalizeOptionsValues($optionsValues)
    {
        $normalizedLabels = [];
        foreach ($optionsValues as $optionsValue) {
            if (!$this->objectFilter->filterObject(
                $optionsValue->getLocale(),
                'pim.internal_api.locale.view'
            )) {
                $normalizedLabels[$optionsValue->getLocale()] = $optionsValue->getValue();
            }
        }

        return $normalizedLabels;
    }
}
