<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace\DTO;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllAppsResult
{
    /** @var array<App> */
    private array $apps;

    /**
     * @param array<App> $apps
     */
    private function __construct(private int $total, array $apps)
    {
        foreach ($apps as $app) {
            if (!$app instanceof App) {
                throw new \InvalidArgumentException(\sprintf(
                    'Expected an array of "%s", got "%s".',
                    App::class,
                    \gettype($app)
                ));
            }
        }
        $this->apps = $apps;
    }

    /**
     * @param array<App> $apps
     */
    public static function create(int $total, array $apps): self
    {
        return new self($total, $apps);
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withAnalytics(array $queryParameters): self
    {
        return self::create(
            $this->total,
            \array_map(fn (App $app): \Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App => $app->withAnalytics($queryParameters), $this->apps),
        );
    }

    /**
     * @param array<string> $queryParameters
     */
    public function withPimUrlSource(array $queryParameters): self
    {
        return self::create(
            $this->total,
            \array_map(fn (App $app): \Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App => $app->withPimUrlSource($queryParameters), $this->apps),
        );
    }

    /**
     * @return array{total:int, apps:mixed[]}
     */
    public function normalize(): array
    {
        $normalizedApps = [];

        foreach ($this->apps as $app) {
            $normalizedApps[] = $app->normalize();
        }

        return [
            'total' => $this->total,
            'apps' => $normalizedApps,
        ];
    }
}
