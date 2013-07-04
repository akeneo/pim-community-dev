<?php

namespace Oro\Bundle\TagBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadTestData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 120;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $account = new Account();
        $account->setName('test');

        /** @var \FPN\TagBundle\Entity\TagManager $tagManager */
        $tagManager = $this->container->get('fpn_tag.tag_manager');

        $tag = $tagManager->loadOrCreateTag('Kharkiv');
        $tagManager->addTag($tag, $account);

        $manager->persist($account);
        $manager->flush($account);

        $tagManager->saveTagging($account);
    }
}
