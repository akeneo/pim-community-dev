<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\FormatApplier;

use Akeneo\Platform\Syndication\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\Syndication\Application\Common\Format\ElementCollection;
use Akeneo\Platform\Syndication\Application\Common\Format\SourceElement;
use Akeneo\Platform\Syndication\Application\Common\Format\TextElement;
use PhpSpec\ObjectBehavior;

class ConcatFormatApplierSpec extends ObjectBehavior
{
    public function it_applies_the_concat_format_without_space_between()
    {
        $format = new ConcatFormat(
            ElementCollection::create([
                new SourceElement('name-uuid'),
                new TextElement('/'),
                new SourceElement('description-uuid'),
            ]),
            false
        );

        $mappedValues = [
            'name-uuid' => 'Nice name',
            'description-uuid' => 'Awesome description',
        ];

        $this->applyFormat($format, $mappedValues)
            ->shouldReturn('Nice name/Awesome description');
    }

    public function it_applies_the_concat_format_with_space_between()
    {
        $format = new ConcatFormat(
            ElementCollection::create([
                new SourceElement('name-uuid'),
                new TextElement('x'),
                new SourceElement('description-uuid'),
            ]),
            true
        );

        $mappedValues = [
            'name-uuid' => 'Nice name',
            'description-uuid' => 'Awesome description',
        ];

        $this->applyFormat($format, $mappedValues)
            ->shouldReturn('Nice name x Awesome description');
    }
}
