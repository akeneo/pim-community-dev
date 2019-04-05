<?php

namespace Akeneo\Pim\Structure\Component\Repository;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Family repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyRepositoryInterface extends
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
{
    /**
     * Returns a querybuilder to get full requirements
     *
     * @param FamilyInterface $family
     * @param string          $localeCode
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFullRequirementsQB(FamilyInterface $family, $localeCode);

    /**
     * Returns all families code with their required attributes code
     * Requirements can be restricted to a channel.
     *
     * @param FamilyInterface  $family
     * @param ChannelInterface $channel
     *
     * @return FamilyInterface[]
     */
    public function getFullFamilies(FamilyInterface $family = null, ChannelInterface $channel = null);

    /**
     * @param array $familyIds
     *
     * @throws \InvalidArgumentException array of id should not be empty
     *
     * @return array
     */
    public function findByIds(array $familyIds);

    /**
     * Checks if a family has the attribute with specified code.
     *
     * @param int    $id
     * @param string $attributeCode
     *
     * @return bool
     */
    public function hasAttribute($id, $attributeCode);
}
