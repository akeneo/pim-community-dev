<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExtensionList
{
    private int $count;

    /** @var array<Extension> */
    private array $extensions;

    /**
     * @param array<Extension> $extensions
     */
    private function __construct(int $count, array $extensions)
    {
        $this->count = $count;
        $this->extensions = $extensions;
    }

    /**
     * @param array<Extension> $extensions
     */
    public static function create(int $count, array $extensions): self
    {
        return new self($count, $extensions);
    }

    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return array<Extension>
     */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return array{count:int, extensions:array<Extension>}
     */
    public function normalize(): array
    {
        return [
            'count' => $this->count,
            'extensions' => $this->extensions,
        ];
    }
}
