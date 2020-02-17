<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Unit
{
    /** @var UnitCode */
    private $code;

    /** @var array */
    private $convertFromStandard;

    /** @var string */
    private $symbol;

    private function __construct(UnitCode $code, array $convertFromStandard, string $symbol)
    {
        Assert::allIsInstanceOf($convertFromStandard, Operation::class);
        Assert::string($symbol);

        $this->code = $code;
        $this->convertFromStandard = $convertFromStandard;
        $this->symbol = $symbol;
    }

    public function create(UnitCode $code, array $convertFromStandard, string $symbol): self
    {
        return new self($code, $convertFromStandard, $symbol);
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'convert_from_standard' => $this->convertFromStandard,
            'symbol' => $this->symbol,
        ];
    }
}
