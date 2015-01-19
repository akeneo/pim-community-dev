<?php
namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;

/**
 * Group repository interface
 *
 * @author    Nicolas Dupont <janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Get ordered groups associative array id to label
     *
     * @param GroupTypeInterface $type
     *
     * @return array
     */
    public function getChoicesByType(GroupTypeInterface $type);

    /**
     * Get groups
     *
     * @return array
     */
    public function getChoices();

    /**
     * Return the number of groups containing the provided attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return int
     */
    public function countVariantGroupAxis(AttributeInterface $attribute);

    /**
     * @return mixed
     */
    public function createDatagridQueryBuilder();

    /**
     * @return mixed
     */
    public function createAssociationDatagridQueryBuilder();

    /**
     * {@inheritdoc}
     */
    public function getOptions($dataLocale, $collectionId = null, $search = '', array $options = array());
}
