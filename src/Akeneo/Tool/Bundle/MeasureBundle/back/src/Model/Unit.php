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
    private UnitCode $code;

    private LabelCollection $labels;

    private array $convertFromStandard;

    private string $symbol;

    private function __construct(UnitCode $code, LabelCollection $labels, array $convertFromStandard, string $symbol)
    {
        Assert::allIsInstanceOf($convertFromStandard, Operation::class);
        Assert::notEmpty($convertFromStandard, 'Expected unit to have at least one operation');

        $this->code = $code;
        $this->labels = $labels;
        $this->convertFromStandard = $convertFromStandard;
        $this->symbol = $symbol;
    }

    public static function create(UnitCode $code, LabelCollection $labels, array $convertFromStandard, string $symbol): self
    {
        return new self($code, $labels, $convertFromStandard, $symbol);
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'labels' => $this->labels->normalize(),
            'convert_from_standard' => array_map(
                static fn (Operation $operation) => $operation->normalize(),
                $this->convertFromStandard
            ),
            'symbol' => $this->symbol,
        ];
    }

    public function code(): UnitCode
    {
        return $this->code;
    }

    public function getLabel(LocaleIdentifier $localeIdentifier): string
    {
        $label = $this->labels->getLabel($localeIdentifier->normalize());

        if (null === $label) {
            $label = sprintf('[%s]', $this->code->normalize());
        }

        return $label;
    }

    public function canBeAStandardUnit(): bool
    {
        return 1 === \count($this->convertFromStandard)
            && Operation::STANDARD_OPERATOR === ($this->convertFromStandard[0])->operator()
            && Operation::STANDARD_VALUE === $this->convertFromStandard[0]->value();
    }
}
