<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Event\AssetFamilyCreatedEvent;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAssetFamilyRepository implements AssetFamilyRepositoryInterface
{
    /** @var AssetFamily[] */
    private $assetFamilies = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(AssetFamily $assetFamily): void
    {
        if (isset($this->assetFamilies[(string) $assetFamily->getIdentifier()])) {
            throw new \RuntimeException('Asset family already exists');
        }
        $this->assetFamilies[(string) $assetFamily->getIdentifier()] = $assetFamily;

        $this->eventDispatcher->dispatch(
            new AssetFamilyCreatedEvent($assetFamily->getIdentifier()),
            AssetFamilyCreatedEvent::class
        );
    }

    public function update(AssetFamily $assetFamily): void
    {
        if (!isset($this->assetFamilies[(string) $assetFamily->getIdentifier()])) {
            throw new \RuntimeException('Expected to save one asset family, but none was saved');
        }
        $this->assetFamilies[(string) $assetFamily->getIdentifier()] = $assetFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): AssetFamily
    {
        foreach ($this->assetFamilies as $identifier => $assetFamily) {
            if (strtolower((string) $identifier) === strtolower((string) $assetFamilyIdentifier)) {
                return $assetFamily;
            }
        }

        throw AssetFamilyNotFoundException::withIdentifier($assetFamilyIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdentifier(AssetFamilyIdentifier $identifier): void
    {
        $assetFamily = $this->assetFamilies[(string) $identifier] ?? null;
        if (null === $assetFamily) {
            throw AssetFamilyNotFoundException::withIdentifier($identifier);
        }

        unset($this->assetFamilies[(string) $identifier]);
    }

    public function count(): int
    {
        return count($this->assetFamilies);
    }

    public function hasAssetFamily(AssetFamilyIdentifier $identifier, bool $caseSensitive = true): bool
    {
        if (true === $caseSensitive) {
            return isset($this->assetFamilies[(string)$identifier]);
        }

        foreach (array_keys($this->assetFamilies) as $assetFamilyIdentifier) {
            if (strtolower((string) $assetFamilyIdentifier) === strtolower((string) $identifier)) {
                return true;
            }
        }

        return false;
    }

    public function all(): \Iterator
    {
        return new \ArrayIterator(array_values($this->assetFamilies));
    }
}
