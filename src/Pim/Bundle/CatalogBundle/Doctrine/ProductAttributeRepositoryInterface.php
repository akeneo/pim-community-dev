<?php
namespace Pim\Bundle\CatalogBundle\Doctrine;

use Oro\Bundle\FlexibleEntityBundle\Model\EntitySet as AbstractEntitySet;
/**
 * Interface to implement
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductAttributeRepositoryInterface
{
    /**
     * Find all attributes excepts already associated to set
     * @param AbstractEntitySet $set
     */
    public function findAllExcept(AbstractEntitySet $set);
}
