<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelCollectionSpec extends ObjectBehavior
{
    public function it_throws_an_exception_when_an_array_key_is_not_string(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            1 => 'Sucre',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_an_array_key_is_an_empty_string(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            '' => 'sucre',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_when_a_value_is_not_a_string(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => 12,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_filters_empty_labels(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => ' ',
        ]]);
        $this->normalize()->shouldBe(['en_US' => 'Sugar']);
    }
}
