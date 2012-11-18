<?php
namespace Pim\Bundle\UIBundle\Grid;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;

/**
 * Provides utility methods to use grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Helper
{
    /**
     * Return relevant grid source for APY grid
     *
     * @param  \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param  string                                     $objectShortname
     * @throws \Exception
     * @return APY\DataGridBundle\Grid\Source\Entity
     */
    public static function getGridSource(\Doctrine\Common\Persistence\ObjectManager $objectManager, $objectShortname)
    {
        // source to create simple grid based on entity or document (ORM or ODM)
        if ($objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
            return new GridDocument($objectShortname);
        } elseif ($objectManager instanceof \Doctrine\ORM\EntityManager) {
            return new GridEntity($objectShortname);
        } else {
            throw new \Exception('There is no grid source for this object manager');
        }
    }
}
