<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\NumberSourceConfiguration;
use PhpSpec\ObjectBehavior;

class SourceConfigurationHydratorSpec extends ObjectBehavior
{
    public function it_hydrates_a_number_source_configuration(): void
    {
        $expected = new NumberSourceConfiguration(',');

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
