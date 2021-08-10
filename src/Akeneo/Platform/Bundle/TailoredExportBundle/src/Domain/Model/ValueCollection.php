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

namespace Akeneo\Platform\TailoredExport\Domain\Model;

use Akeneo\Platform\TailoredExport\Domain\Model\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Domain\Model\Source\SourceInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;

final class ValueCollection
{
    /** @var array<string, SourceValueInterface> */
    private array $values = [];

    public function add(
        SourceValueInterface $value,
        string $code,
        ?string $channelReference,
        ?string $localeReference
    ): void {
        if (!$this->has($code, $channelReference, $localeReference)) {
            $this->values[$this->getKey($code, $channelReference, $localeReference)] = $value;
        }
    }

    public function get(
        string $code,
        ?string $channelReference,
        ?string $localeReference
    ): SourceValueInterface {
        return $this->values[$this->getKey($code, $channelReference, $localeReference)];
    }

    public function getFromSource(SourceInterface $source): SourceValueInterface
    {
        if ($source instanceof AttributeSource) {
            return $this->get($source->getCode(), $source->getChannel(), $source->getLocale());
        } elseif ($source instanceof PropertySource) {
            return $this->get($source->getName(), null, null);
        } elseif ($source instanceof AssociationTypeSource) {
            return $this->get($source->getCode(), null, null);
        } else {
            throw new \InvalidArgumentException('Unsupported source');
        }
    }

    public function has(
        string $code,
        ?string $channelReference,
        ?string $localeReference
    ): bool {
        return array_key_exists($this->getKey($code, $channelReference, $localeReference), $this->values);
    }

    private function getKey(
        string $code,
        ?string $channelReference,
        ?string $localeReference
    ): string {
        return sprintf(
            '%s-%s-%s',
            $code,
            $channelReference ?? '<all_channels>',
            $localeReference ?? '<all_locales>'
        );
    }
}
