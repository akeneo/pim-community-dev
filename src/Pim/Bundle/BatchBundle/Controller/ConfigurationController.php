<?php
namespace Pim\Bundle\BatchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\FormInterface;
use Pim\Bundle\BatchBundle\Entity\RawConfiguration;

/**
 * Configuration controller
 *
 * @Route("/configuration")
 *
 */
class ConfigurationController extends Controller
{

    /**
     * Edit configuration
     *
     * @param Configuration $entity
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(RawConfiguration $entity)
    {
        $configuration = $entity->getConfiguration();
        $type = $configuration->getFormTypeServiceId();
        $form = $this->createForm($type, $configuration);

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->submit($this->getRequest());
            if ($form->isValid()) {
                $manager = $this->getDoctrine()->getEntityManager();
                $manager->persist($entity);
                $manager->flush();
                $this->get('session')->getFlashBag()->add('success', 'Configuration successfully saved');

                return $this->redirect($this->generateUrl('pim_batch_connector_index'));
            }
        }

        return array('form' => $form->createView(), 'configuration' => $entity);
    }
}
