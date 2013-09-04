<?php

namespace Pim\Bundle\CatalogBundle\Tests\Functional\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Test related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserPreferencesListenerTest extends WebTestCase
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        $this->userManager = $container->get('oro_user.manager');
        $this->entityManager = $container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Tests attribute removal when a channel is updated
     */
    public function testRemoveChannel()
    {
        $prefix = sha1(rand(0, 9999999));
        $channel1 = new Channel;
        $channel1->setCode($prefix . 'channel1')->setName('channel1');
        $this->entityManager->persist($channel1);

        $channel2 = new Channel;
        $channel2->setCode($prefix . 'channel2')->setName('channel2');
        $this->entityManager->persist($channel2);

        $this->entityManager->flush();

        $attribute = $this->userManager->getFlexibleRepository()->findAttributeByCode('catalogscope');
        foreach ($attribute->getOptions() as $option) {
            if (($prefix . 'channel2') == $option->getOptionValue()->getValue()) {
                $removedOption = $option;
            }
        }

        $user = $this->userManager->createFlexible();
        $user
                ->setUsername($prefix)
                ->setEmail($prefix . '@test.com')
                ->setPassword($prefix);
        $value = $user->setCatalogscope($removedOption);
        $this->userManager->updateUser($user);
        $this->entityManager->flush();

        $this->entityManager->remove($channel2);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $user = $this->userManager->findUserByUsername($prefix);
        $channel1 = $this->entityManager->getRepository('PimCatalogBundle:Channel')->find($channel1->getId());
        $this->assertNotEquals(
            $removedOption->getOptionValue()->getValue(),
            $user->getCatalogscope()->getData()->getOptionValue()->getValue()
        );

        $this->entityManager->remove($channel1);
        $this->entityManager->remove($user);
        $this->entityManager->flush();

    }
}
