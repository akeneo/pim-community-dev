<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Create;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateGeneratorCommandSpec extends ObjectBehavior
{
    private const VALID_IDENTIFIER = [
        'code' => 'my_new_generator',
        'labels' => [
            'en_US' => 'My new generator',
            'fr_FR' => 'Mon nouveau générateur',
        ],
        'target' => 'sku',
        'conditions' => [],
        'structure' => [[
            'type' => 'free_text',
            'string' => 'AKN',
        ]],
        'delimiter' => null,
        'text_transformation' => 'no',
    ];

    public function it_should_create_a_command(): void
    {
        $this->beConstructedThrough('fromNormalized', [self::VALID_IDENTIFIER]);
        $this->shouldNotThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_create_a_command_if_key_is_missing(): void
    {
        foreach (array_keys(self::VALID_IDENTIFIER) as $key) {
            $invalid_command = self::VALID_IDENTIFIER;
            unset($invalid_command[$key]);
            $this->beConstructedThrough('fromNormalized', [$invalid_command]);
            $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
        }
    }

    public function it_should_not_create_a_command_if_code_is_not_a_string(): void
    {
        $invalid_command = self::VALID_IDENTIFIER;
        $invalid_command['code'] = ['a_code_in_an_array'];
        $this->beConstructedThrough('fromNormalized', [$invalid_command]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_create_a_command_if_target_is_not_a_string(): void
    {
        $invalid_command = self::VALID_IDENTIFIER;
        $invalid_command['target'] = ['a_target_in_an_array'];
        $this->beConstructedThrough('fromNormalized', [$invalid_command]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_create_if_delimiter_is_not_a_string(): void
    {
        $invalid_command = self::VALID_IDENTIFIER;
        $invalid_command['delimiter'] = ['a_delimiter_in_an_array'];
        $this->beConstructedThrough('fromNormalized', [$invalid_command]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_create_a_command_if_conditions_is_not_an_array(): void
    {
        $invalid_command = self::VALID_IDENTIFIER;
        $invalid_command['conditions'] = 'a_condition_as_string';
        $this->beConstructedThrough('fromNormalized', [$invalid_command]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_create_a_command_if_structure_is_not_an_array(): void
    {
        $invalid_command = self::VALID_IDENTIFIER;
        $invalid_command['structure'] = 'a_structure_as_string';
        $this->beConstructedThrough('fromNormalized', [$invalid_command]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_not_create_a_command_if_labels_are_not_an_array(): void
    {
        $invalid_command = self::VALID_IDENTIFIER;
        $invalid_command['labels'] = 'labels_as_string';
        $this->beConstructedThrough('fromNormalized', [$invalid_command]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
