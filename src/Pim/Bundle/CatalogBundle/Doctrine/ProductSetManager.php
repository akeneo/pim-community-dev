<?php
namespace Pim\Bundle\CatalogBundle\Doctrine;

use Oro\Bundle\FlexibleEntityBundle\Doctrine\BaseEntityManager;

/**
 * Manager product sets
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSetManager extends BaseEntityManager
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
     * Clone an entity type
     *
     * @param EntitySet $entitySet to clone
     *
     * @return EntitySet
     *
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
    }*/

}
