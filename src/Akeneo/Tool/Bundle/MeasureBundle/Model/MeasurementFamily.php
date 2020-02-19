<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamily
{
    /** @var string */
    private $code;

    /** @var LabelCollection */
    private $labels;

    /** @var UnitCode */
    private $standardUnit;

    /** @var array */
    private $units;

    private function __construct(string $code, array $labels, UnitCode $standardUnit, array $units)
    {
        Assert::stringNotEmpty($code);
        Assert::isInstanceOf($labels, LabelCollection::class);
        Assert::allIsInstanceOf($units, Unit::class);
        Assert::minCount($units, 1);

        $this->code = $code;
        $this->labels = $labels;
        $this->standardUnit = $standardUnit;
        $this->units = $units;
    }

    public function create(string $code, array $labels, UnitCode $standardUnit, array $units): self
    {
        return new self($code, $labels, $standardUnit, $units);
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'labels' => $this->labels->normalize(),
            'standard_unit' => $this->standardUnit->normalize(),
            'units' => array_map(
                function (UnitCode $code) {
                    return $code->normalize();
                },
                $this->units
            )
        ];
    }
}
