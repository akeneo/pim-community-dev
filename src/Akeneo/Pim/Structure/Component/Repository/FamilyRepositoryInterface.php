<?php

namespace Akeneo\Pim\Structure\Component\Repository;

use Doctrine\ORM\QueryBuilder;
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
     */
    public function getFullRequirementsQB(FamilyInterface $family, string $localeCode): QueryBuilder;

    /**
     * Returns all families code with their required attributes code
     * Requirements can be restricted to a channel.
     *
     * @param FamilyInterface  $family
     * @param ChannelInterface $channel
     *
     * @return FamilyInterface[]
     */
    public function getFullFamilies(FamilyInterface $family = null, ChannelInterface $channel = null): array;

    /**
     * @param array $familyIds
     *
     * @throws \InvalidArgumentException array of id should not be empty
     */
    public function findByIds(array $familyIds): array;

    /**
     * Checks if a family has the attribute with specified code.
     *
     * @param int    $id
     * @param string $attributeCode
     */
    public function hasAttribute(int $id, string $attributeCode): bool;

    /**
     * Get families with family variants
     */
    public function getWithVariants(string $search = null, array $options = [], int $limit = null): array;
}
