<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

/**
 * Object that represent the converted field (family, category, etc.) from a flat file data (CSV, XLSX)
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertedField
{
    /** @var string */
    private $columnName;

    /** @var mixed */
    private $value;

    /**
     * @param string $columnName
     * @param mixed  $value
     */
    public function __construct(string $columnName, $value)
    {
        $this->columnName = $columnName;
        $this->value = $value;
    }

    /**
     * @param $convertedField
     *
     * @return array
     */
    public function appendTo($convertedField): array
    {
        if (array_key_exists($this->columnName, $convertedField)) {
            $convertedField[$this->columnName] = array_merge_recursive($convertedField[$this->columnName], $this->value);
        } else {
            $convertedField[$this->columnName] = $this->value;
        }

        return $convertedField;
    }
}
