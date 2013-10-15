<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SetupStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        return $this->render(
            'OroInstallerBundle:Process/Step:setup.html.twig',
            array(
                'form' => $this->createForm('oro_installer_setup')->createView()
            )
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        $form = $this->createForm('oro_installer_setup');

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            // load demo fixtures
            if ($form->has('loadFixtures') && $form->get('loadFixtures')->getData()) {
                $em     = $this->getDoctrine()->getManager();
                $loader = new ContainerAwareLoader($this->container);

                foreach ($this->get('kernel')->getBundles() as $bundle) {
                    if (is_dir($path = $bundle->getPath() . '/DataFixtures/Demo')) {
                        $loader->loadFromDirectory($path);
                    }
                }

                $executor = new ORMExecutor($em);

                foreach ($loader->getFixtures() as $f) {
                        echo get_class($f) . '<br>';
                }
                $executor->execute($loader->getFixtures(), true);
            }

            $user = $form->getData();
            $role = $this
                ->getDoctrine()
                ->getRepository('OroUserBundle:Role')
                ->findOneBy(array('role' => 'ROLE_ADMINISTRATOR'));

            $user
                ->setEnabled(true)
                ->setOwner($this->get('oro_organization.business_unit_manager')->getBusinessUnit())
                ->addRole($role);

            $this->get('oro_user.manager')->updateUser($user);

            return $this->complete();
        }

        return $this->render(
            'OroInstallerBundle:Process/Step:setup.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }
}
