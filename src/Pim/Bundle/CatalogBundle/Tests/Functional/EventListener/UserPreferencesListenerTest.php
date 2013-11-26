<?php

namespace Pim\Bundle\CatalogBundle\Tests\Functional\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Channel;

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
    protected $userManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

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

        $category = new Category();
        $category->setCode($prefix .'TEST_TREE');
        $this->entityManager->persist($category);

        $channel1 = new Channel();
        $channel1
            ->setCode($prefix . 'channel1')
            ->setLabel('channel1')
            ->setCategory($category);
        $this->entityManager->persist($channel1);

        $channel2 = new Channel();
        $channel2
            ->setCode($prefix . 'channel2')
            ->setLabel('channel2')
            ->setCategory($category);
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
            ->setPassword($prefix)
            ->setCatalogscope($removedOption);
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

        $attribute = $this->userManager->getFlexibleRepository()->findAttributeByCode('catalogscope');
        foreach ($attribute->getOptions() as $option) {
            $this->assertNotEquals($prefix . 'channel2', $option->getOptionValue()->getValue());
        }
    }

    /**
     * Create a channel entity
     *
     * @param string $code
     * @param string $label
     * @param string $category
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function createChannel($code, $label, $category)
    {
        $channel = new Channel();
        $channel
            ->setCode($code)
            ->setLabel($label)
            ->setCategory($category);

        return $channel;
    }
}
