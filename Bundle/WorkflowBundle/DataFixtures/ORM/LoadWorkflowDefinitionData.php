<?php

namespace Oro\Bundle\WorkflowBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider;

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
        $configurationProvider = $this->container->get('oro_workflow.configuration_provider');

        foreach ($configurationProvider->getWorkflowDefinitions() as $workflowDefinition) {
            $manager->persist($workflowDefinition);
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
