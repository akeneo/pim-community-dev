<?php
namespace Pim\Bundle\CatalogBundle\Doctrine;

use Oro\Bundle\FlexibleEntityBundle\Doctrine\BaseEntityManager;

/**
 * Manager product template
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateManager extends BaseEntityManager
{

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getEntityShortname()
    {
        return 'PimCatalogBundle:ProductSet';
    }

    /**
     * Return shortname that can be used to get the repository or instance
     * @return string
     */
    public function getGroupShortname()
    {
        return 'PimCatalogBundle:ProductGroup';
    }

    /**
     * Return implementation class that can be use to instanciate
     * @return string
    */
    public function getGroupClass()
    {
        return $this->manager->getClassMetadata($this->getGroupShortname())->getName();
    }

    /**
     * Return related repository
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    public function getGroupRepository()
    {
        return $this->manager->getRepository($this->getAttributeShortname());
    }

    /**
     * Return a new instance
     * @return Entity
     */
    public function getNewGroupInstance()
    {
        $class = $this->getGroupClass();

        return new $class();
    }

    /**
     * Clone an entity type
     *
     * @param EntitySet $entitySet to clone
     *
     * @return EntitySet
     */
    public function cloneSet($entitySet)
    {
        // create new entity type and clone values
        $cloneSet = $this->getNewEntityInstance();
        $cloneSet->setCode($entitySet->getCode());
        $cloneSet->setTitle($entitySet->getTitle());

        // clone groups
        foreach ($entitySet->getGroups() as $groupToClone) {

            // clone group entity
            // TODO : we have to know the group manager
            $cloneGroup = $this->getNewGroupInstance();
            $cloneGroup->setTitle($groupToClone->getTitle());
            $cloneGroup->setCode($groupToClone->getCode());
            $cloneSet->addGroup($cloneGroup);

            // link to same attributes
            foreach ($groupToClone->getAttributes() as $attToLink) {
                $cloneGroup->addAttribute($attToLink);
            }
        }

        return $cloneSet;
    }

}
