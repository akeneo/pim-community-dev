<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File\Converter;

use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Exception\InvalidOptionsException;

/**
 * Attribute Option CSV Converter
 *
 * @author    Nicolas Dupont <nicola@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: rename to ArrayConverter + use Flat or Structured
 */
class CsvAttributeOptionToStandardConverter implements StandardFormatConverterInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(LocaleRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'label-de_DE': '210 x 1219 mm',
     *     'label-en_US': '210 x 1219 mm',
     *     'label-fr_FR': '210 x 1219 mm'
     * }
     *
     * After:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
     * }
     *
     * @param array $item Representing a flat attribute option
     *
     * @return array structured product
     *
     * @throws InvalidOptionsException
     */
    public function convert($item)
    {
        $optionResolver = $this->createOptionsResolver();
        $resolvedItem = $optionResolver->resolve($item);
        $convertedItem = ['labels' => []];
        foreach ($resolvedItem as $field => $data) {
            $isLabel = false !== strpos($field, 'label-', 0);
            if ($isLabel) {
                $labelTokens = explode('-', $field);
                $labelLocale = $labelTokens[1];
                $convertedItem['labels'][$labelLocale] = $data;
            } else {
                $convertedItem[$field] = $data;
            }
        }

        return $convertedItem;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();

        $required = ['code', 'attribute', 'sort_order'];
        $defaults = ['sort_order' => 1];
        $allowedTypes = [
            'code' => 'string',
            'attribute' => 'string',
            'sort_order' => 'string',
        ];

        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $code) {
            $labelField = 'label-'.$code;
            $required[] = $labelField;
            $allowedTypes[$labelField] = 'string';
        }

        $resolver->setRequired($required);
        $resolver->setDefaults($defaults);
        $resolver->setAllowedTypes($allowedTypes);

        return $resolver;
    }
}
