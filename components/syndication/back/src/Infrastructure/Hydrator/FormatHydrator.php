<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Hydrator;

use Akeneo\Platform\Syndication\Application\Common\Format\ConcatFormat;
use Akeneo\Platform\Syndication\Application\Common\Format\FormatInterface;
use Akeneo\Platform\Syndication\Application\Common\Format\NoneFormat;

class FormatHydrator
{
    private ElementCollectionHydrator $elementCollectionHydrator;

    public function __construct(
        ElementCollectionHydrator $elementCollectionHydrator
    ) {
        $this->elementCollectionHydrator = $elementCollectionHydrator;
    }

    public function hydrate(array $normalizedFormat): FormatInterface
    {
        if ($normalizedFormat['type'] === 'concat') {
            return new ConcatFormat(
                $this->elementCollectionHydrator->hydrate($normalizedFormat['elements']),
                $normalizedFormat['space_between']
            );
        }
        if ($normalizedFormat['type'] === 'none') {
            return new NoneFormat(
                $this->elementCollectionHydrator->hydrate([])
            );
        }

        throw new \InvalidArgumentException(sprintf('Unsupported format type "%s"', $normalizedFormat['type']));
    }
}
