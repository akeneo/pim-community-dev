<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class StructuredAttributeOptionNormalizer extends AttributeOptionNormalizer
{
    /** @var array */
    protected $supportedFormat = ['json'];

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param SimpleFactoryInterface    $attributeOptionValueFactory
     * @param ObjectFilterInterface     $objectFilter
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
