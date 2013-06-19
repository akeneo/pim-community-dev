<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;

/**
 * User controller.
 *
 * @Route("/flex")
 */
class ConfigController extends Controller
{
    /**
     * Lists all Flexible entities.
     *
     * @Route("/", name="flex")
     * @Template()
     */
    public function indexAction()
    {

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(ConfigEntity::ENTITY_NAME)->findAll();

//        $entities = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
//        foreach ($entities as $entity){
//            var_dump( $em->getClassMetadata($entity) );
//            die;
//        }


//        var_dump( $em->getClassMetadata('Oro\Bundle\UserBundle\Entity\User') );
//        var_dump( $entities );
        //die;

        return array(
            'entities' => $entities
        );
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}/show", name="flex_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Config::ENTITY_NAME)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
}
