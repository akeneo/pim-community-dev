<?php

namespace Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;

/**
 * Convert flat format to standard format for attribute option
 *
 * @author    Nicolas Dupont <nicola@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOption implements ArrayConverterInterface
{
    public function __construct(
        protected FindLocales $findLocales,
        protected FieldsRequirementChecker $fieldChecker
    ) {
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
     *     'sort_order': '2',
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
        $this->fieldChecker->checkFieldsPresence($item, ['attribute', 'code']);
        $this->fieldChecker->checkFieldsFilling($item, ['attribute', 'code']);

        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            if ('code' === $field || 'attribute' === $field) {
                $convertedItem[$field] = (string) $data;
            } elseif ('sort_order' === $field) {
                $convertedItem[$field] = (int) $data;
            } elseif (\preg_match('/^label-(?P<locale>[\w_]+)$/', $field, $matches)) {
                $locale = $this->findLocales->find($matches['locale']);
                if (null === $locale || !$locale->isActivated()) {
                    $this->unexpectedFieldException($field);
                }
                $convertedItem['labels'][$locale->getCode()] = $data;
            } else {
                $this->unexpectedFieldException($field);
            }
        }

        return $convertedItem;
    }

    /**
     * @throws StructureArrayConversionException
     */
    protected function unexpectedFieldException(string $field): never
    {
        $authorizedFields = ['attribute', 'code', 'sort_order'];
        $authorizedFields = \array_merge(
            $authorizedFields,
            \array_map(
                static fn (Locale $locale): string => \sprintf('label-%s', $locale->getCode()),
                $this->findLocales->findAllActivated()
            )
        );

        throw new StructureArrayConversionException(
            \sprintf(
                'Field "%s" is provided, authorized fields are: "%s"',
                $field,
                \implode(', ', $authorizedFields)
            )
        );
    }
}
