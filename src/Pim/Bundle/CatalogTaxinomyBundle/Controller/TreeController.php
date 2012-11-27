<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Controller;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/tree")
 *
 */
class TreeController extends Controller
{
    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    protected function getRepository()
    {
        return $this->getEntityManager()
                    ->getRepository('Pim\Bundle\CatalogTaxinomyBundle\Entity\Category');
    }

    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $repo = $this->getEntityManager()->getRepository('Pim\Bundle\CatalogTaxinomyBundle\Entity\Category');
        $food = $repo->findOneByTitle('food');

        echo $repo->childCount($food);
    }
}