<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\Common\Persistence\ObjectManager;
/**
 * Service class to manage categories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     * @param ObjectManager $objectManager
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get all children for a parent category id
     * @param integer $parentId
     *
     * @return ArrayCollection
     */
    public function getChildren($parentId)
    {
        return $this->getRepository()->findBy(array('parent' => $parentId));
    }

    /**
     * Get repository
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->objectManager->getRepository('PimCatalogTaxinomyBundle:Category');
    }
}