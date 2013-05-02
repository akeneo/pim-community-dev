<?php
namespace Pim\Bundle\ConfigBundle\Controller;

use FOS\Rest\Util\Codes;

use FOS\RestBundle\Routing\ClassResourceInterface;

use FOS\RestBundle\Controller\FOSRestController;

use FOS\RestBundle\Controller\Annotations\NamePrefix;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

/**
 * Implements API REST channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/apirestchannel")
 */
class ApiRestChannelController extends FOSRestController implements ClassResourceInterface
{

    /**
     * Delete channel
     *
     * @param int $id channel id
     *
     * @ApiDoc(
     *     description="Delete channel",
     *     resource=true,
     *     requirements={
     *         {"name"="id", "dataType"="integer"},
     *     }
     * )
     *
     * @Route("/delete/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $entity = $this->getDoctrine()->getEntityManager()->getRepository('PimConfigBundle:Channel')->findOneBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->getDoctrine()->getEntityManager()->remove($entity);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }
}
