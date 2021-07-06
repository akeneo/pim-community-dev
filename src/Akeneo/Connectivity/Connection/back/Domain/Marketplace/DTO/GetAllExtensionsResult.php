<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\DTO;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllExtensionsResult
{
    private int $total;

    /** @var array<Extension> */
    private array $extensions;

    /**
     * @param array<Extension> $extensions
     */
    private function __construct(int $total, array $extensions)
    {
        $this->total = $total;
        $this->extensions = $extensions;
    }

    /**
     * @param array<Extension> $extensions
     */
    public static function create(int $total, array $extensions): self
    {
        return new self($total, $extensions);
    }

    /**
     * @return array{total:int, extensions:array}
     */
    public function normalize(): array
    {
        $normalizedExtensions = [];

        foreach ($this->extensions as $extension) {
            $normalizedExtensions[] = $extension->normalize();
        }

        return [
            'total' => $this->total,
            'extensions' => $normalizedExtensions,
        ];
    }
}
