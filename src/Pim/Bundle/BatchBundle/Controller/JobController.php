<?php
namespace Pim\Bundle\BatchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\FormInterface;
use Pim\Bundle\BatchBundle\Form\Type\JobType;
use Pim\Bundle\BatchBundle\Entity\Connector;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Entity\RawConfiguration;

/**
 * Job controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/job")
 */
class JobController extends Controller
{

    /**
     * Create job
     *
     * @param Connector $connector
     *
     * @Route("/create/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template("PimBatchBundle:Job:edit.html.twig")
     *
     * @return array
     */
    public function createAction(Connector $connector)
    {
        $entity = new Job();
        $entity->setConnector($connector);

        return $this->editAction($entity);
    }

    /**
     * Edit job
     *
     * @param Job $entity
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Job $entity)
    {
        $connectorToJobIds = $this->container->get('pim_batch.connectors')->getConnectorToJobs();
        $connector = $entity->getConnector();
        $serviceIds = $connectorToJobIds[$connector->getServiceId()];
        $form = $this->createForm(new JobType(), $entity, array('serviceIds' => $serviceIds));

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->submit($this->getRequest());
            if ($form->isValid()) {

                if (is_null($entity->getRawConfiguration())) {
                    $this->addDefaultConfiguration($entity);
                }

                $manager = $this->getDoctrine()->getEntityManager();
                $manager->persist($entity);
                $manager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Job successfully saved');
                $url = $this->generateUrl('pim_batch_connector_index');

                return $this->redirect($url);
            }
        }

        return array('form' => $form->createView(), 'job' => $entity, 'connector' => $connector);
    }

    /**
     * Add a default configuration
     *
     * @param Job $entity
     */
    protected function addDefaultConfiguration(Job $entity)
    {
        $service = $this->container->get($entity->getServiceId());
        $configurationClassName = $service->getConfigurationName();
        $entity->setRawConfiguration(new RawConfiguration(new $configurationClassName()));
    }

    /**
     * @param Job $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(Job $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Job successfully removed');

        return $this->redirect($this->generateUrl('pim_batch_connector_index'));
    }
}
