<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;

/**
 * Category Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param FieldsRequirementChecker $fieldChecker
     */
    public function __construct(
        FieldsRequirementChecker $fieldChecker,
        LocaleRepositoryInterface $localeRepository
    )
    {
        $this->fieldChecker = $fieldChecker;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'code'        => 'mycode',
     *      'parent'      => 'master',
     *      'label-fr_FR' => 'T-shirt super beau',
     *      'label-en_US' => 'T-shirt very beautiful',
     * ]
     *
     * After:
     * [
     *      'code'   => 'mycode',
     *      'parent' => 'master',
     *      'labels' => [
     *          'fr_FR' => 'T-shirt super beau',
     *          'en_US' => 'T-shirt very beautiful',
     *      ],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);

        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
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
        } elseif ('code' === $field && '' !== $data) {
            $convertedItem[$field] = (string) $data;
        } elseif ('parent' === $field && '' !== $data) {
            $convertedItem[$field] = $data;
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     * @return void
     * @throws StructureArrayConversionException
     */
    protected function validate(array $item)
    {
        $requiredFields = ['code'];

        $this->fieldChecker->checkFieldsPresence($item, $requiredFields);
        $this->fieldChecker->checkFieldsFilling($item, $requiredFields);

        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $code) {
            $requiredFields[] = 'label-' . $code;
        }

        foreach (array_keys($item) as $field) {
            if (!in_array($field, $requiredFields)) {
                throw new StructureArrayConversionException(
                    sprintf(
                        'Field "%s" is provided, authorized fields are: "%s"',
                        $field,
                        implode(', ', $requiredFields)
                    )
                );
            }
        }
    }
}
