<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

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
        set_time_limit(60);

        $form = $this->createForm('oro_installer_setup');

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            // load demo fixtures
            if ($form->has('load_fixtures') && $form->get('load_fixtures')->getData()) {

            }

            $user = $form->getData();
            $role = $this
                ->getDoctrine()
                ->getRepository('OroUserBundle:Role')
                ->findOneBy(array('role' => 'ROLE_SUPER_ADMIN'));

            $user->setEnabled(true)
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
