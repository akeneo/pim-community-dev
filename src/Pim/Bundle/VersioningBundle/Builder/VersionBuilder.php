<?php

namespace Pim\Bundle\VersioningBundle\Builder;

use Symfony\Component\Serializer\SerializerInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\UpdateGuesser\VersionableUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\TranslationsUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ContainsProductsUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeOptionUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ProductValueUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeGroupUpdateGuesser;

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
        $this->serializer   = $serializer;
    }

    /**
     * Build a version from a versionable entity
     *
     * @param VersionableInterface $versionable
     * @param User                 $user
     *
     * @return Version
     */
    public function buildVersion(VersionableInterface $versionable, User $user)
    {
        $resourceName = get_class($versionable);
        $resourceId   = $versionable->getId();
        $numVersion   = $versionable->getVersion();
        // TODO: we don't use direct json serialize due to convert to audit data based on array_diff
        $data         = $this->serializer->normalize($versionable, 'csv');

        return new Version($resourceName, $resourceId, $numVersion, $data, $user);
    }

    /**
     * Check if some entities must be versioned due to an entity changes
     *
     * @param EntityManager $em
     * @param object        $entity
     *
     * @return array
     */
    public function checkScheduledUpdate($em, $entity)
    {
        $pendings = array();

        $guessers = array(
            new VersionableUpdateGuesser(),
            new TranslationsUpdateGuesser(),
            new ContainsProductsUpdateGuesser(),
            new AttributeOptionUpdateGuesser(),
            new ProductValueUpdateGuesser(),
            new AttributeOptionUpdateGuesser(),
            new AttributeGroupUpdateGuesser()
        );

        foreach ($guessers as $guesser) {
            $updates = $guesser->guessUpdates($em, $entity);
            $pendings = array_merge($pendings, $updates);
        }

        return $pendings;
    }
}
