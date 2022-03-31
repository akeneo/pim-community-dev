<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\NumberSourceParameter;
use PhpSpec\ObjectBehavior;

class SourceParameterHydratorSpec extends ObjectBehavior
{
    public function it_hydrates_a_number_source_parameter(): void
    {
        $expected = new NumberSourceParameter(',');

        $this->hydrate(
            ['decimal_separator' => ','],
            'pim_catalog_number',
        )->shouldBeLike($expected);
    }

    public function it_returns_null_by_default(): void
    {
        $this->hydrate(null, 'pim_catalog_text')
            ->shouldReturn(null);
    }
}
