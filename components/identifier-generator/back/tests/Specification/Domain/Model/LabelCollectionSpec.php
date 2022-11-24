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

    public function it_can_be_instantiated_with_a_stdclass(): void
    {
        $this->beConstructedThrough('fromNormalized', [new \stdClass()]);

        $this->normalize()->shouldBeLike((object) []);
    }

    public function it_normalizes_labels(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => '',
        ]]);
        $this->normalize()->shouldBe(['en_US' => 'Sugar']);
    }

    public function it_normalizes_empty_label(): void
    {
        $this->beConstructedThrough('fromNormalized', [[]]);

        $this->normalize()->shouldBeLike((object) []);
    }

    public function it_can_be_merged_with_other_labels(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => 'Suc',
        ]]);

        $newLabels = $this->merge(['fr_FR' => 'Sucre', 'de_DE' => 'Zucker', 'en_US' => '']);
        $newLabels->shouldNotBe($this);
        $newLabels->shouldBeLike(LabelCollection::fromNormalized([
            'fr_FR' => 'Sucre',
            'de_DE' => 'Zucker',
        ]));
    }

    public function it_can_be_merged_with_an_empty_array(): void
    {
        $this->beConstructedThrough(
            'fromNormalized',
            [
                [
                    'en_US' => 'Sugar',
                    'fr_FR' => '',
                ],
            ]
        );

        $newLabels = $this->merge([]);
        $newLabels->shouldNotBe($this);
        $newLabels->shouldBeLike(LabelCollection::fromNormalized(['en_US' => 'Sugar']));
        $newLabels->normalize()->shouldReturn(['en_US' => 'Sugar']);
    }

    public function it_returns_a_label(): void
    {
        $this->beConstructedThrough('fromNormalized', [[
            'en_US' => 'Sugar',
            'fr_FR' => 'Suc',
        ]]);
        $this->getLabel('en_US')->shouldReturn('Sugar');
        $this->getLabel('fr_FR')->shouldReturn('Suc');
        $this->getLabel('de_DE')->shouldReturn(null);
    }
}
