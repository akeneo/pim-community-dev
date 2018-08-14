<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;

/**
 * Group Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Group implements ArrayConverterInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /**
     * @param LocaleRepositoryInterface  $localeRepository
     * @param FieldsRequirementChecker   $fieldChecker
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        FieldsRequirementChecker $fieldChecker
    ) {
        $this->localeRepository = $localeRepository;
        $this->fieldChecker = $fieldChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Convert flat array to structured array:
     *
     * Before:
     * [
     *      'code'        => 'group1',
     *      'type'        => 'RELATED',
     *      'label-de_DE' => '',
     *      'label-en_US' => '',
     *      'label-fr_FR' => '',
     * ]
     *
     * After:
     * [
     *     'code'   => 'mycode',
     *     'type'   => 'RELATED',
     *     'labels' => [
     *         'en_US' => 'T-shirt very beautiful',
     *         'fr_FR' => 'T-shirt super beau',
     *     ],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);
        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            if ('' !== $data) {
                $convertedItem = $this->convertField($convertedItem, $field, $data);
            }
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField($convertedItem, $field, $data)
    {
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        }

        switch ($field) {
            case 'code':
            case 'type':
                $convertedItem[$field] = $data;
                break;
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     */
    protected function validate(array $item)
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code', 'type']);
        $this->fieldChecker->checkFieldsFilling($item, ['code', 'type']);
        $this->validateAuthorizedFields($item, ['type', 'code']);
    }

    /**
     * @param array $item
     * @param array $authorizedFields
     *
     * @throws StructureArrayConversionException
     */
    protected function validateAuthorizedFields(array $item, array $authorizedFields)
    {
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $code) {
            $authorizedFields[] = 'label-' . $code;
        }

        foreach (array_keys($item) as $field) {
            if (!in_array($field, $authorizedFields)) {
                throw new StructureArrayConversionException(
                    sprintf(
                        'Field "%s" is provided, authorized fields are: "%s"',
                        $field,
                        implode(', ', $authorizedFields)
                    )
                );
            }
        }
    }
}
