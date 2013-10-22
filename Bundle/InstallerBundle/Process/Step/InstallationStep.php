<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class InstallationStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(900);

        switch ($this->getRequest()->query->get('action')) {
            case 'fixtures':
                $loader = new ContainerAwareLoader($this->container);

                foreach ($this->get('kernel')->getBundles() as $bundle) {
                    if (is_dir($path = $bundle->getPath() . '/DataFixtures/Demo')) {
                        $loader->loadFromDirectory($path);
                    }
                }

                $executor = new ORMExecutor($this->getDoctrine()->getManager());

                $executor->execute($loader->getFixtures(), true);

                return $this->getRequest()->isXmlHttpRequest()
                    ? new JsonResponse(array('result' => true))
                    : $this->redirect('');
            case 'schema':
                return $this->handleAjaxAction('doctrine:schema:update', array('--force' => true));
            case 'search':
                return $this->handleAjaxAction('oro:search:create-index');
            case 'navigation':
                return $this->handleAjaxAction('oro:navigation:init');
            case 'assets':
                return $this->handleAjaxAction('assets:install', array('target' => './'));
            case 'assetic':
                return $this->handleAjaxAction('assetic:dump');
            case 'assetic-oro':
                return $this->handleAjaxAction('oro:assetic:dump');
            case 'translation':
                return $this->handleAjaxAction('oro:translation:dump');
            case 'requirejs':
                return $this->handleAjaxAction('oro:requirejs:build');
        }

        return $this->render(
            'OroInstallerBundle:Process/Step:installation.html.twig',
            array(
                'loadFixtures' => $context->getStorage()->get('loadFixtures'),
            )
        );
    }
}
