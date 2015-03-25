<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\Flat;

use Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter\StandardArrayConverterInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Attribute Option Flat Converter
 *
 * @author    Nicolas Dupont <nicola@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionToStandardConverter implements StandardArrayConverterInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var OptionsResolverInterface */
    protected $optionResolver;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(LocaleRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     *
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
     */
    public function convert(array $item, array $options = [])
    {
        $optionResolver = $this->getOptionsResolverInstance();
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
     * Get the same instance in case of flat format, assuming that each converted item will have same headers
     *
     * @return OptionsResolverInterface
     */
    protected function getOptionsResolverInstance()
    {
        if (null === $this->optionResolver) {
            $this->optionResolver = new OptionsResolver();

            $required = ['code', 'attribute', 'sort_order'];
            $defaults = ['sort_order' => 1];
            $allowedTypes = [
                'code' => 'string',
                'attribute' => 'string',
                'sort_order' => 'int'
            ];

            $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
            foreach ($localeCodes as $code) {
                $labelField = 'label-' . $code;
                $required[] = $labelField;
                $allowedTypes[$labelField] = 'string';
            }

            $this->optionResolver->setRequired($required);
            $this->optionResolver->setDefaults($defaults);
            $this->optionResolver->setAllowedTypes($allowedTypes);
            $integerNormalizer = function ($options, $value) {
                return (int) $value;
            };
            $this->optionResolver->setNormalizers(['sort_order' => $integerNormalizer]);
        }

        return $this->optionResolver;
    }
}
