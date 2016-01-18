<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface;

/**
 * Family repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyRepositoryInterface extends
    ChoicesProviderInterface,
    IdentifiableObjectRepositoryInterface,
    ObjectRepository
{
    /**
     * @param object $qb
     * @param bool   $inset
     * @param mixed  $values
     *
     * @deprecated will be removed in 1.4
     */
    public function applyMassActionParameters($qb, $inset, $values);

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
     * @return array
     */
    public function getFullFamilies(FamilyInterface $family = null, ChannelInterface $channel = null);

    /**
     * @return mixed
     */
    public function createDatagridQueryBuilder();

    /**
     * Find attribute ids from family ids
     *
     * @param array $familyIds
     *
     * @return array '<f_id>' => array(<attribute ids>)
     */
    public function findAttributeIdsFromFamilies(array $familyIds);

    /**
     * @param array $familyIds
     *
     * @throws \InvalidArgumentException array of id should not be empty
     *
     * @return array
     */
    public function findByIds(array $familyIds);

    /**
     * Return the number of existing families
     *
     * @return int
     */
    public function countAll();
}
