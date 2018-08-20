<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollection
{
    private $valuesIndexedByAttribute = [];

    /** @var Value[] */
    private $values = [];

    /**
     * @param Value[] $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * @return Value[]
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * @return Value[]
     */
    public function indexedByAttribute(): array
    {
        return $this->valuesIndexedByAttribute;
    }

    private function add(Value $value)
    {
        $attributeCode = $value->attributeCode();
        $this->valuesIndexedByAttribute[$attributeCode][] = $value;
        $this->values[] = $value;
    }

}
