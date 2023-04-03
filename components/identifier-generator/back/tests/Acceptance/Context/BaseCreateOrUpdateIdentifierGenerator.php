<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorHandler;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseCreateOrUpdateIdentifierGenerator
{
    public const DEFAULT_IDENTIFIER_GENERATOR_CODE = 'generator_0';

    public function __construct(
        protected readonly ViolationsContext $violationsContext,
        protected readonly CreateGeneratorHandler $createGeneratorHandler,
        protected readonly UpdateGeneratorHandler $updateGeneratorHandler,
    ) {
    }

    protected function tryToCreateGenerator(
        ?string $code = null,
        ?array $structure = null,
        ?array $conditions = null,
        ?array $labels = null,
        ?string $target = null,
        ?string $delimiter = null,
        ?string $textTransformation = null,
    ): void {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                $code ?? self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                $conditions ?? [],
                $structure ?? [['type' => 'free_text', 'string' => self::DEFAULT_IDENTIFIER_GENERATOR_CODE]],
                $labels ?? ['fr_FR' => 'Générateur'],
                $target ?? 'sku',
                $delimiter ?? '-',
                $textTransformation ?? 'no',
            ));
        } catch (ViolationsException $exception) {
            $this->violationsContext->setViolationsException($exception);
        }
    }

    protected function tryToUpdateGenerator(
        ?string $code = null,
        ?array $structure = null,
        ?array $conditions = null,
        ?array $labels = null,
        ?string $target = null,
        ?string $delimiter = null,
        ?string $textTransformation = null,
    ): void {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                $code ?? self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                $conditions ?? [],
                $structure ?? [['type' => 'free_text', 'string' => self::DEFAULT_IDENTIFIER_GENERATOR_CODE]],
                $labels ?? ['fr_FR' => 'Générateur'],
                $target ?? 'sku',
                $delimiter ?? 'updatedGenerator',
                $textTransformation ?? 'no',
            ));
        } catch (ViolationsException $violations) {
            $this->violationsContext->setViolationsException($violations);
        }
    }

    protected function getValidCondition(string $type, ?string $operator = null): array
    {
        switch($type) {
            case 'enabled': return [
                'type' => 'enabled',
                'value' => true,
            ];
            case 'family': return [
                'type' => 'family',
                'operator' => $operator ?? 'IN',
                'value' => ['tshirt'],
            ];
            case 'simple_select': return [
                'type' => 'simple_select',
                'operator' => $operator ?? 'IN',
                'attributeCode' => 'color',
                'value' => ['green'],
            ];
            case 'reference_entity': return [
                'type' => 'reference_entity',
                'operator' => 'NOT IN',
                'attributeCode' => 'brand',
                'value' => ['akeneo'],
            ];
            case 'multi_select': return [
                'type' => 'multi_select',
                'operator' => $operator ?? 'IN',
                'attributeCode' => 'a_multi_select',
                'value' => ['option_a', 'option_b'],
            ];
            case 'category': return [
                'type' => 'category',
                'operator' => $operator ?? 'IN',
                'value' => ['tshirts'],
            ];
        }

        throw new \InvalidArgumentException('Unknown type ' . $type . ' for getValidCondition');
    }
}
