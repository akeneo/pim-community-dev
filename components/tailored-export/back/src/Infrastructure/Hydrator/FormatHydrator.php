<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredExport\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\TailoredExport\Application\Common\Format\FormatInterface;

class FormatHydrator
{
    public function __construct(
        private ElementCollectionHydrator $elementCollectionHydrator,
    ) {
    }

    public function hydrate(array $normalizedFormat): FormatInterface
    {
        if ('concat' !== $normalizedFormat['type']) {
            throw new \InvalidArgumentException(sprintf('Unsupported format type "%s"', $normalizedFormat['type']));
        }

        return new ConcatFormat(
            $this->elementCollectionHydrator->hydrate($normalizedFormat['elements']),
            $normalizedFormat['space_between'],
        );
    }
}
