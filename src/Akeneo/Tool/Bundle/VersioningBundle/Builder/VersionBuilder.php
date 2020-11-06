<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Builder;

use Akeneo\Tool\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var VersionFactory */
    protected $versionFactory;

    /**
     * @param NormalizerInterface $normalizer
     * @param VersionFactory      $versionFactory
     */
    public function __construct(NormalizerInterface $normalizer, VersionFactory $versionFactory)
    {
        $this->normalizer = $normalizer;
        $this->versionFactory = $versionFactory;
    }

    /**
     * Build a version for a versionable entity
     *
     * @param object       $versionable
     * @param string       $author
     * @param Version|null $previousVersion
     * @param string|null  $context
     */
    public function buildVersion(object $versionable, string $author, Version $previousVersion = null, ?string $context = null): \Akeneo\Tool\Component\Versioning\Model\Version
    {
        $resourceName = ClassUtils::getClass($versionable);
        $resourceId = $versionable->getId();

        $versionNumber = $previousVersion !== null ? $previousVersion->getVersion() + 1 : 1;
        $oldSnapshot = $previousVersion !== null ? $previousVersion->getSnapshot() : [];

        // TODO: we don't use direct json serialize due to convert to audit data based on array_diff
        $snapshot = $this->normalizer->normalize($versionable, 'flat', []);

        $changeset = $this->buildChangeset($oldSnapshot, $snapshot);

        $version = $this->versionFactory->create($resourceName, $resourceId, $author, $context);
        $version->setVersion($versionNumber)
            ->setSnapshot($snapshot)
            ->setChangeset($changeset);

        return $version;
    }

    /**
     * Create a pending version for a versionable entity
     *
     * @param object      $versionable
     * @param string      $author
     * @param array       $changeset
     * @param string|null $context
     */
    public function createPendingVersion(object $versionable, string $author, array $changeset, ?string $context = null): \Akeneo\Tool\Component\Versioning\Model\Version
    {
        $version = $this->versionFactory->create(
            ClassUtils::getClass($versionable),
            $versionable->getId(),
            $author,
            $context
        );
        $version->setChangeset($changeset);

        return $version;
    }

    /**
     * Build a pending version
     *
     * @param Version      $pending
     * @param Version|null $previousVersion
     */
    public function buildPendingVersion(Version $pending, Version $previousVersion = null): \Akeneo\Tool\Component\Versioning\Model\Version
    {
        $versionNumber = $previousVersion !== null ? $previousVersion->getVersion() + 1 : 1;
        $oldSnapshot = $previousVersion !== null ? $previousVersion->getSnapshot() : [];

        $modification = $pending->getChangeset();
        $snapshot = $modification + $oldSnapshot;
        $changeset = $this->buildChangeset($oldSnapshot, $snapshot);

        $pending->setVersion($versionNumber)
            ->setSnapshot($snapshot)
            ->setChangeset($changeset);

        return $pending;
    }

    /**
     * Build the changeset
     *
     * @param array $oldSnapshot
     * @param array $newSnapshot
     */
    protected function buildChangeset(array $oldSnapshot, array $newSnapshot): array
    {
        return $this->filterChangeset($this->mergeSnapshots($oldSnapshot, $newSnapshot));
    }

    /**
     * Merge the old and new snapshots
     *
     * @param array $oldSnapshot
     * @param array $newSnapshot
     */
    protected function mergeSnapshots(array $oldSnapshot, array $newSnapshot): array
    {
        $localNewSnapshot = array_map(
            fn($newItem) => ['new' => $newItem],
            $newSnapshot
        );

        $localOldSnapshot = array_map(
            fn($oldItem) => ['old' => $oldItem],
            $oldSnapshot
        );

        $mergedSnapshot = array_replace_recursive($localNewSnapshot, $localOldSnapshot);

        return array_map(
            fn($mergedItem) => [
                'old' => array_key_exists('old', $mergedItem) ? $mergedItem['old'] : '',
                'new' => array_key_exists('new', $mergedItem) ? $mergedItem['new'] : ''
            ],
            $mergedSnapshot
        );
    }

    /**
     * Filter changeset to remove values that are the same
     *
     * @param array $changeset
     */
    protected function filterChangeset(array $changeset): array
    {
        return array_filter(
            $changeset,
            fn($item) => $this->hasValueChanged($item['old'], $item['new'])
        );
    }

    private function hasValueChanged($old, $new): bool
    {
        if (null !== $hasChanged = $this->hasLegacyDateChanged($old, $new)) {
            return $hasChanged;
        }

        return $old !== $new;
    }

    /**
     * We need to handle date comparison for old versioning format 'Y-m-d' in place of the new 'Y-m-d\TH:i:sP'.
     *
     * To determine that we are comparing date from the old versioning format:
     * - Check that the old value can be interpreted as a date with the format 'Y-m-d'
     * - Check that the new value can be interpreted as a date with the format 'Y-m-d\TH:i:sP'
     * - If both match the expected format, then we compare them as date.
     *
     * If one of the value doesn't match an expected date format, then it's not an issue (or not a date) and we fallback
     * to the standard behavior.
     *
     * @see https://akeneo.atlassian.net/browse/PIM-9152
     *
     * @return bool|null True if the date has changed, False otherwise. Null if the comparison can't be done.
     */
    private function hasLegacyDateChanged($old, $new): ?bool
    {
        if (!is_string($old) || !is_string($new)) {
            return null;
        }

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
}
