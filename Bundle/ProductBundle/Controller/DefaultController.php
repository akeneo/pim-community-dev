<?php
namespace Oro\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Default controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/default")
 */
class DefaultController extends Controller
{

    /**
     * Get product manager
     * @return FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * @Route("/truncatedb")
     * @Template("OroProductBundle:Default:index.html.twig")
     *
     * @return multitype
     */
    public function truncatedbAction()
    {
        // update schema / truncate db
        $em = $this->getProductManager()->getStorageManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadatas)) {
            $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $tool->dropSchema($metadatas);
            $tool->createSchema($metadatas);
        }

        $this->get('session')->setFlash('notice', "DB has been truncated with success (schema re-generation)");

        return $this->redirect($this->generateUrl('oro_product_product_index'));
    }

}
