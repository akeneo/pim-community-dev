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

namespace Akeneo\Platform\Syndication\Application\Common;

use Akeneo\Platform\Syndication\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\AttributeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\Source\SourceInterface;
use Akeneo\Platform\Syndication\Application\Common\Source\StaticSource;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;

final class ValueCollection
{
    /** @var array<string, SourceValueInterface> */
    private array $values = [];

    public function add(
        SourceValueInterface $value,
        string $uuid,
        ?string $channelReference,
        ?string $localeReference
    ): void {
        if (!$this->has($uuid, $channelReference, $localeReference)) {
            $this->values[$this->getKey($uuid, $channelReference, $localeReference)] = $value;
        }
    }

    public function get(
        string $uuid,
        ?string $channelReference,
        ?string $localeReference
    ): SourceValueInterface {
        return $this->values[$this->getKey($uuid, $channelReference, $localeReference)];
    }

    public function getFromSource(SourceInterface $source): SourceValueInterface
    {
        if ($source instanceof AttributeSource) {
            return $this->get($source->getUuid(), $source->getChannel(), $source->getLocale());
        } elseif ($source instanceof PropertySource) {
            return $this->get($source->getUuid(), null, null);
        } elseif ($source instanceof StaticSource) {
            return $this->get($source->getUuid(), null, null);
        } elseif ($source instanceof AssociationTypeSource) {
            return $this->get($source->getUuid(), null, null);
        } else {
            throw new \InvalidArgumentException('Unsupported source');
        }
    }

    public function has(
        string $uuid,
        ?string $channelReference,
        ?string $localeReference
    ): bool {
        return array_key_exists($this->getKey($uuid, $channelReference, $localeReference), $this->values);
    }

    private function getKey(
        string $uuid,
        ?string $channelReference,
        ?string $localeReference
    ): string {
        return sprintf(
            '%s-%s-%s',
            $uuid,
            $channelReference ?? '<all_channels>',
            $localeReference ?? '<all_locales>'
        );
    }
}
