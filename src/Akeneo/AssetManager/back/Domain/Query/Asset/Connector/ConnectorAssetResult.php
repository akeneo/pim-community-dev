<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Query\Asset\Connector;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class ConnectorAssetResult
{
    /** @var ConnectorAsset[] */
    private array $assets;
    private ?array $lastSortValue;

    private function __construct(array $assets, ?array $lastSortValue)
    {
        $this->assets = $assets;
        $this->lastSortValue = $lastSortValue;
    }

    public static function createFromSearchAfterQuery(array $assets, ?array $lastSortValue)
    {
        Assert::allIsInstanceOf($assets, ConnectorAsset::class);

        return new self($assets, $lastSortValue);
    }

    /**
     * @return ConnectorAsset[]
     */
    public function assets(): array
    {
        return $this->assets;
    }

    public function lastSortValue(): ?array
    {
        return $this->lastSortValue;
    }
}
