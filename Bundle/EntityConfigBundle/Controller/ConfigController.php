<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\GridBundle\Datagrid\Datagrid;
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
 * @Route("/oro_entityconfig")
 */
class ConfigController extends Controller
{
    /**
     * Lists all Flexible entities.
     *
     * @Route("/", name="oro_entityconfig_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /** @var Datagrid $datagrid */
        $datagrid = $this->get('oro_entity_config.datagrid.manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityConfigBundle:Config:index.html.twig';

        return $this->render(
            $view,
            array(
                'datagrid' => $datagrid->createView()
            )
        );
    }

    /**
     * @Route("/view/{id}", name="oro_entityconfig_view", requirements={"id"="\d+"})
     * @Template
     */
    public function viewAction(EntityConfig $entity)
    {
        return array(
            'entity' => $entity,
        );
    }
}
