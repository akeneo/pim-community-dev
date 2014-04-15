<?php

namespace Pim\Bundle\VersioningBundle\Builder;

use Symfony\Component\Serializer\SerializerInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Build a version from a versionable entity
     *
     * @param object       $versionable
     * @param User         $user
     * @param Version|null $previousVersion
     * @param string|null  $context
     *
     * @return Version
     */
    public function buildVersion($versionable, User $user, Version $previousVersion = null, $context = null)
    {
        $resourceName = get_class($versionable);
        $resourceId   = $versionable->getId();

        $versionNumber = $previousVersion ? $previousVersion->getVersion() + 1 : 1;
        $oldSnapshot   = $previousVersion ? $previousVersion->getSnapshot() : [];

        // TODO: we don't use direct json serialize due to convert to audit data based on array_diff
        $snapshot = $this->serializer->normalize($versionable, 'csv', array('versioning' => true));

        $changeset = $this->buildChangeset($oldSnapshot, $snapshot);

        return new Version($resourceName, $resourceId, $versionNumber, $snapshot, $changeset, $user, $context);
    }


    /**
     * Build the changeset
     *
     * @param array $oldSnapshot
     * @param array $newSnapshot
     *
     * @return array
     */
    protected function buildChangeset(array $oldSnapshot, array $newSnapshot)
    {
        return $this->filterChangeset($this->mergeSnapshots($oldSnapshot, $newSnapshot));
    }

    /**
     * Merge the old and new snapshots
     *
     * @param array $oldSnapshot
     * @param array $newSnapshot
     *
     * @return array
     */
    protected function mergeSnapshots(array $oldSnapshot, array $newSnapshot)
    {
        $newSnapshot = array_map(
            function ($newItem) {
                return ['new' => $newItem];
            },
            $newSnapshot
        );

        $oldSnapshot = array_map(
            function ($oldItem) {
                return ['old' => $oldItem];
            },
            $oldSnapshot
        );

        $mergedSnapshot = array_merge_recursive($newSnapshot, $oldSnapshot);

        return array_map(
            function ($mergedItem) {
                return [
                    'old' => array_key_exists('old', $mergedItem) ? $mergedItem['old'] : '',
                    'new' => array_key_exists('new', $mergedItem) ? $mergedItem['new'] : ''
                ];
            },
            $mergedSnapshot
        );
    }

    /**
     * Filter changeset to remove values that are the same
     *
     * @param array $changeset
     *
     * @return array
     */
    protected function filterChangeset(array $changeset)
    {
        return array_filter(
            $changeset,
            function ($item) {
                return $item['old'] != $item['new'];
            }
        );
    }
}
