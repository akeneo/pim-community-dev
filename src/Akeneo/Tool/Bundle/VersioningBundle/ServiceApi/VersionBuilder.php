<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\ServiceApi;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository;
use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Component\Versioning\Model\Version;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    public function __construct(
        private readonly VersionFactory $versionFactory,
        private readonly VersionRepository $versionRepository,
    )
    {
    }

    public function buildVersionWithId(?string $resourceId, string $resourceName, array $snapshot, string $author): Version
    {
        $previousVersion = $this->versionRepository->getNewestLogEntry(
            resourceName: $resourceName,
            resourceId: $resourceId,
            resourceUuid: null
        );
        $versionNumber = $previousVersion ? $previousVersion->getVersion() + 1 : 1;
        $oldSnapshot = $previousVersion ? $previousVersion->getSnapshot() : [];

        $changeset = $this->buildChangeset($oldSnapshot, $snapshot);

        $version = $this->versionFactory->create($resourceName, $resourceId, null, 'admin');
        $version->setVersion($versionNumber)
            ->setSnapshot($snapshot)
            ->setChangeset($changeset);

        return $version;
    }

    private function buildChangeset(array $oldSnapshot, array $newSnapshot)
    {
        return $this->filterChangeset($this->mergeSnapshots($oldSnapshot, $newSnapshot));
    }

    private function filterChangeset(array $changeset)
    {
        return array_filter(
            $changeset,
            function ($item) {
                return $this->hasValueChanged($item['old'], $item['new']);
            }
        );
    }

    private function hasValueChanged($old, $new): bool
    {
        if (null !== $hasChanged = $this->hasLegacyDateChanged($old, $new)) {
            return $hasChanged;
        }

        return $old !== $new;
    }

    private function hasLegacyDateChanged($old, $new): bool | null
    {
        if (!is_string($old) || !is_string($new)) {
            return null;
        }

        $old = str_replace(chr(0), '', $old);
        $new = str_replace(chr(0), '', $new);

        $oldDateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $old, new \DateTimeZone('UTC'));
        if (false === $oldDateTime) {
            return null;
        }
        $oldDateTime = $oldDateTime->setTime(0, 0);

        $newDateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $new);
        if (false === $newDateTime) {
            return null;
        }

        return $oldDateTime->format('U') !== $newDateTime->format('U');
    }

    protected function mergeSnapshots(array $oldSnapshot, array $newSnapshot)
    {
        $localNewSnapshot = array_map(
            static function ($newItem) {
                return ['new' => $newItem];
            },
            $newSnapshot
        );

        $localOldSnapshot = array_map(
            static function ($oldItem) {
                return ['old' => $oldItem];
            },
            $oldSnapshot
        );

        $mergedSnapshot = array_replace_recursive($localNewSnapshot, $localOldSnapshot);

        return array_map(
            static function ($mergedItem) {
                return [
                    'old' => array_key_exists('old', $mergedItem) ? $mergedItem['old'] : '',
                    'new' => array_key_exists('new', $mergedItem) ? $mergedItem['new'] : ''
                ];
            },
            $mergedSnapshot
        );
    }

}
