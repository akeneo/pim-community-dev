<?php

namespace Pim\Component\Connector\ArrayConverter\Structured;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 *  Convert structured format to standard format for job instance
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceConverter implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /**
     * @param FieldsRequirementChecker $fieldChecker
     */
    public function __construct(FieldsRequirementChecker $fieldChecker)
    {
        $this->fieldChecker = $fieldChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Before:
     * [
     *      'connector'     => 'Data fixtures',
     *      'alias'         => 'fixtures_currency_csv',
     *      'label'         => 'Currencies data fixtures',
     *      'type'          => 'type'
     *      'configuration' => [
     *          'filePath' => 'currencies.csv'
     *      ],
     *      'code'          => 'fixtures_currency_csv',
     * ]
     *
     * After:
     * [
     *      'connector'     => 'Data fixtures',
     *      'alias'         => 'fixtures_currency_csv',
     *      'label'         => 'Currencies data fixtures',
     *      'type'          => 'type'
     *      'configuration' => [
     *          'filePath' => 'currencies.csv'
     *      ],
     *      'code'          => 'fixtures_currency_csv',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code', 'type', 'connector', 'label', 'alias']);
        $this->fieldChecker->checkFieldsFilling($item, ['code', 'type', 'connector', 'label']);

        return $item;
    }
}
