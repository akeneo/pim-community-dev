<?php

namespace Strixos\DataFlowBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Strixos\CatalogBundle\DataFixtures\ORM\LoadAttributeSetData;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }

    /**
    * @Route("/loaddemodata")
    * @Template()
    */
    public function loadDemoDataAction()
    {
        // TODO: truncate before

        // load attribute sets and attributes
        $em = $this->getDoctrine()->getEntityManager();
        $loader = new LoadAttributeSetData();
        $loader->load($em);

        return new Response('Reset ok');
    }

    /**
    * @Route("/loadmagentodata")
    * @Template()
    */
    public function loadMagentoDataAction()
    {
        // load job by code
        $job = $this->getDoctrine()
            ->getRepository('StrixosDataFlowBundle:Job')
            ->findOneBy(array('code' => 'Import Magento Product'));
        // setup entity and document manager TODO inject in other way
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $em = $this->getDoctrine()->getEntityManager();
        $job->setDocumentManager($dm);
        $job->setEntityManager($em);
        // run
        $job->run();
        $message = 'Magento Data has been loaded with success! '.implode(', ', $job->getMessages());
        $this->get('session')->setFlash('notice', $message);
        return $this->redirect($this->generateUrl('pim_dashboard_default_index'));
    }
}
