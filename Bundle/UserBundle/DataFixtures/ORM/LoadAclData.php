<?php
namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Acl;

class LoadAclData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load Root ACL Resource
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $rootAcl = new Acl();
        $rootAcl->setId('root')
            ->setDescription('root node')
            ->setName('Root')
            ->addAccessRole($this->getReference('user_role'))
            ->addAccessRole($this->getReference('admin_role'));
        $manager->persist($rootAcl);
        $this->setReference('acl_root', $rootAcl);

        // security section
        $oroSecurity = new Acl();
        $oroSecurity->setId('oro_security')
            ->setName('Oro Security')
            ->setDescription('Oro security')
            ->setParent($this->getReference('acl_root'))
            ->addAccessRole($this->getReference('anon_role'));
        $manager->persist($oroSecurity);
        $this->setReference('acl_oro_security', $oroSecurity);

        $oroLogin = new Acl();
        $oroLogin->setId('oro_login')
            ->setName('Login page')
            ->setDescription('Oro Login page')
            ->setParent($this->getReference('acl_oro_security'));
        $manager->persist($oroLogin);
        $this->setReference('acl_oro_login', $oroLogin);

        $oroLoginCheck = new Acl();
        $oroLoginCheck->setId('oro_login_check')
            ->setName('Login check')
            ->setDescription('Oro Login check')
            ->setParent($this->getReference('acl_oro_security'));
        $manager->persist($oroLoginCheck);
        $this->setReference('acl_oro_login_check', $oroLoginCheck);

        $oroLogout = new Acl();
        $oroLogout->setId('oro_logout')
            ->setName('Logout')
            ->setDescription('Oro Logout')
            ->setParent($this->getReference('acl_oro_security'));
        $manager->persist($oroLogout);
        $this->setReference('acl_oro_logout', $oroLogout);

        $this->addResetAcl($manager);

        $manager->flush();
    }

    private function addResetAcl(ObjectManager $manager)
    {
        $oroReset = new Acl();
        $oroReset->setId('oro_reset_controller')
            ->setName('Reset user password')
            ->setDescription('Oro Reset user password')
            ->setParent($this->getReference('acl_oro_security'))
            ->addAccessRole($this->getReference('anon_role'));
        $manager->persist($oroReset);
        $this->setReference('acl_oro_reset_controller', $oroReset);

        $oroReset = new Acl();
        $oroReset->setId('oro_reset_request')
            ->setName('reset password')
            ->setDescription('Oro Reset password page')
            ->setParent($this->getReference('acl_oro_reset_controller'));
        $manager->persist($oroReset);
        $this->setReference('acl_oro_reset_request', $oroReset);

        $oroReset = new Acl();
        $oroReset->setId('oro_reset_send_mail')
            ->setName('send reset mail')
            ->setDescription('Request reset user password')
            ->setParent($this->getReference('acl_oro_reset_controller'));
        $manager->persist($oroReset);
        $this->setReference('acl_oro_reset_send_mail', $oroReset);

        $oroReset = new Acl();
        $oroReset->setId('oro_reset_check_email')
            ->setName('reset password check email')
            ->setDescription('Tell the user to check his email provider')
            ->setParent($this->getReference('acl_oro_reset_controller'));
        $manager->persist($oroReset);
        $this->setReference('acl_oro_reset_check_email', $oroReset);

        $oroReset = new Acl();
        $oroReset->setId('oro_reset_password')
            ->setName('reset password')
            ->setDescription('Reset user password')
            ->setParent($this->getReference('acl_oro_reset_controller'));
        $manager->persist($oroReset);
        $this->setReference('acl_oro_reset_password', $oroReset);
    }

    public function getOrder()
    {
        return 100;
    }
}
