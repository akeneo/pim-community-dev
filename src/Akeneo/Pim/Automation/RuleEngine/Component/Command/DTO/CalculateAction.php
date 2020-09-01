<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

final class CalculateAction implements ActionInterface
{
    public $source;
    public $destination;
    public $operationList;
    public $roundPrecision;

    public function __construct(array $data)
    {
        $destination = $data['destination'] ?? null;
        if (is_array($destination)) {
            $destination = new ProductDestination($destination);
        }
        $this->destination = $destination;

        $source = $data['source'];
        if (is_array($source)) {
            $source = new Operand($source);
        }
        $this->source = $source;

        $operationList = $data['operation_list'] ?? null;
        if (is_array($operationList)) {
            $operationList = array_map(
                function ($operation) {
                    return is_array($operation) ? new Operation($operation) : $operation;
                },
                $operationList
            );
        }
        $this->operationList = $operationList;

        $this->roundPrecision = $data['round_precision'] ?? null;
    }

    public function toArray(): array
    {
        Assert::isInstanceOf($this->destination, ProductDestination::class);
        Assert::isInstanceOf($this->source, Operand::class);
        Assert::isArray($this->operationList);
        Assert::allIsInstanceOf($this->operationList, Operation::class);
        Assert::nullOrInteger($this->roundPrecision);

        return array_filter([
            'type' => 'calculate',
            'destination' => $this->destination->toArray(),
            'source' => $this->source->toArray(),
            'operation_list' => array_map(function (Operation $operation): array {
                return $operation->toArray();
            }, $this->operationList),
            'round_precision' => $this->roundPrecision,
        ], function ($value): bool {
            return null !== $value;
        });
    }
}
