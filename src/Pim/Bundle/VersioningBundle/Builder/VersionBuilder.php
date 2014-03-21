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
        $oldData       = $previousVersion ? $previousVersion->getData() : [];

        // TODO: we don't use direct json serialize due to convert to audit data based on array_diff
        $data = $this->serializer->normalize($versionable, 'csv', array('versioning' => true));

        $changeset = $this->buildDiffData($oldData, $data);

        return new Version($resourceName, $resourceId, $versionNumber, $data, $changeset, $user, $context);
    }


    /**
     * Build diff data
     *
     * @param array $oldData
     * @param array $newData
     *
     * @return array
     */
    protected function buildDiffData(array $oldData, array $newData)
    {
        return $this->filterDiffData($this->getMergedData($oldData, $newData));
    }

    /**
     * Merge the old and new data
     *
     * @param array $oldData
     * @param array $newData
     *
     * @return array
     */
    protected function getMergedData(array $oldData, array $newData)
    {
        $newData = array_map(
            function ($newItem) {
                return ['new' => $newItem];
            },
            $newData
        );

        $oldData = array_map(
            function ($oldItem) {
                return ['old' => $oldItem];
            },
            $oldData
        );

        $mergedData = array_merge_recursive($newData, $oldData);

        return array_map(
            function ($mergedItem) {
                return [
                    'old' => array_key_exists('old', $mergedItem) ? $mergedItem['old'] : '',
                    'new' => array_key_exists('new', $mergedItem) ? $mergedItem['new'] : ''
                ];
            },
            $mergedData
        );
    }

    /**
     * Filter diff data to remove values that are the same
     *
     * @param array $diffData
     *
     * @return array
     */
    protected function filterDiffData(array $diffData)
    {
        return array_filter(
            $diffData,
            function ($diffItem) {
                return $diffItem['old'] != $diffItem['new'];
            }
        );
    }
}
