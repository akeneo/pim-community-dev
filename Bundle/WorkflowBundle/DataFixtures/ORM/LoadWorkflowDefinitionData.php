<?php

namespace Oro\Bundle\WorkflowBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationWorkflowDefinitionBuilder;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowDefinitionRepository;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;

class LoadWorkflowDefinitionData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var ConfigurationProvider $configurationProvider */
        $configurationProvider = $this->container->get('oro_workflow.configuration.config_provider');
        /** @var ConfigurationWorkflowDefinitionBuilder $configurationBuilder */
        $configurationBuilder = $this->container->get('oro_workflow.configuration.builder.workflow_definition');

        $workflowConfiguration = $configurationProvider->getWorkflowDefinitionConfiguration();
        $workflowDefinitions = $configurationBuilder->buildFromConfiguration($workflowConfiguration);

        /** @var WorkflowDefinitionRepository $workflowDefinitionRepository */
        $workflowDefinitionRepository = $manager->getRepository('OroWorkflowBundle:WorkflowDefinition');
        foreach ($workflowDefinitions as $workflowDefinition) {
            /** @var WorkflowDefinition $existingWorkflowDefinition */
            $existingWorkflowDefinition = $workflowDefinitionRepository->find($workflowDefinition->getName());

            // workflow definition should be overridden if workflow definition with such name already exists
            if ($existingWorkflowDefinition) {
                $existingWorkflowDefinition->import($workflowDefinition);
            } else {
                $manager->persist($workflowDefinition);
            }
        }

        $manager->flush();
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
