<?php
namespace Pim\Bundle\BatchBundle\Controller;

use Pim\Bundle\BatchBundle\Job\JobExecution;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\FormInterface;
use Pim\Bundle\BatchBundle\Form\Type\ConnectorType;
use Pim\Bundle\BatchBundle\Entity\Connector;
use Pim\Bundle\BatchBundle\Entity\RawConfiguration;

/**
 * Connector controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/connector")
 */
class ConnectorController extends Controller
{

    /**
     * Select a connector
     *
     * @Route("/index")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getEntityManager()->getRepository('PimBatchBundle:Connector');
        $entities = $repository->findAll();

        return array('connectors' => $entities);
    }

    /**
     * Create connector
     *
     * @Route("/create")
     * @Template("PimBatchBundle:Connector:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $entity = new Connector();

        return $this->editAction($entity);
    }

    /**
     * Edit connector
     *
     * @param Connector $entity
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Connector $entity)
    {
        $serviceIds = array_keys($this->container->get('pim_batch.connectors')->getConnectorToJobs());
        $form = $this->createForm(new ConnectorType(), $entity, array('serviceIds' => $serviceIds));

        // process form
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->submit($this->getRequest());
            if ($form->isValid()) {

                if (is_null($entity->getRawConfiguration())) {
                    $this->addDefaultConfiguration($entity);
                }

                $manager = $this->getDoctrine()->getEntityManager();
                $manager->persist($entity);
                $manager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Connector successfully saved');
                $url = $this->generateUrl('pim_batch_connector_index');

                return $this->redirect($url);
            }
        }

        return array('form' => $form->createView(), 'connector' => $entity);
    }

    /**
     * Add a default configuration
     *
     * @param Connector $entity
     */
    protected function addDefaultConfiguration(Connector $entity)
    {
        $service = $this->container->get($entity->getServiceId());
        $configurationClassName = $service->getConfigurationName();
        $entity->setRawConfiguration(new RawConfiguration(new $configurationClassName()));
    }

    /**
     * @param Connector $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(Connector $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Connector successfully removed');

        return $this->redirect($this->generateUrl('pim_batch_connector_index'));
    }

    /**
     * Run
     *
     * @param Connector $entity
     *
     * @Route("/run/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     *
     * @return array
     */
    public function runAction(Connector $entity)
    {
        if ($this->getRequest()->getMethod() === 'POST') {
            $confConnector = $entity->getRawConfiguration()->getConfiguration();

            // TODO deal with jobs order (depends on scheduler ?)
            foreach ($entity->getJobs() as $job) {
                $confJob = $job->getRawConfiguration()->getConfiguration();
                $service = $this->get($job->getServiceId());
                $service->configure($confConnector, $confJob);
                $service->execute(new JobExecution());
                $this->get('session')->getFlashBag()->add('success', 'Run job '.$job->getServiceId());
            }
        }

        return array('connector' => $entity);
    }
}
