<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class CalculateAction
{
    public $source;
    public $destination;
    public $operationList;

    public function __construct(array $data)
    {
        $destination = $data['destination'] ?? null;
        if (is_array($destination)) {
            $destination = new ProductTarget($destination);
        }
        $this->destination = $destination;

        $source = $data['source'];
        if (is_array($source)) {
            $source = new Operand($source);
        }
        $this->source = $source;

        $operationList = $data['operation_list'] ?? null;
        if (is_array($operationList)) {
            $operationList = array_map(function($operation) {
                return is_array($operation) ? new Operation($operation) : $operation;
            }, $operationList);
        }
        $this->operationList = $operationList;
    }
}
