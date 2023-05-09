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
    /** @var array<Extension> */
    private array $extensions;

    /**
     * @param array<Extension> $extensions
     */
    private function __construct(private int $total, array $extensions)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof Extension) {
                throw new \InvalidArgumentException(\sprintf(
                    'Expected an array of "%s", got "%s".',
                    Extension::class,
                    \gettype($extension)
                ));
            }
        }
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
     * @param array<string> $queryParameters
     */
    public function withAnalytics(array $queryParameters): self
    {
        return self::create(
            $this->total,
            \array_map(fn (Extension $extension): \Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension => $extension->withAnalytics($queryParameters), $this->extensions),
        );
    }

    /**
     * @return array{total:int, extensions:mixed[]}
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
