<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\Domain\Query;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-immutable
 */
final class ConnectorCategory
{
    /**
     * @param array<string,string> $translations ['en_US' => 'jambon']
     */
    public function __construct(
        public string $code,
        public ?string $parentCode,
        public int $position,
        public array $translations,
    ) {
    }

    public function toArray()
    {
        return [
            'code' => $this->code,
            'parent' => $this->parentCode,
            'position' => $this->position,
            'translations' => empty($this->translations) ? (object) [] : $this->translations
        ];
    }
}
