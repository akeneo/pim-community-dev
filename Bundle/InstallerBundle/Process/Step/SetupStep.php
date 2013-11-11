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
        $form = $this->createForm('oro_installer_setup');

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            // pass "load demo fixtures" flag to the next step
            $context->getStorage()->set('loadFixtures', $form->has('loadFixtures') && $form->get('loadFixtures')->getData());

            $user = $form->getData();
            $role = $this
                ->getDoctrine()
                ->getRepository('OroUserBundle:Role')
                ->findOneBy(array('role' => 'ROLE_ADMINISTRATOR'));

            $businessUnit = $this
                ->getDoctrine()
                ->getRepository('OroOrganizationBundle:BusinessUnit')
                ->findOneBy(array('name' => 'Main'));

            $user
                ->setEnabled(true)
                ->setOwner($businessUnit)
                ->addBusinessUnit($businessUnit)
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
