<?php

namespace Pim\Bundle\UserBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * Load required user data for PIM
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $userRepository;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container      = $container;
        $this->userManager    = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $users = $this->userRepository->findAll();

        $localeCodes   = $this->getLocaleManager()->getActiveCodes();
        $localeCode = in_array('en_US', $localeCodes) ? 'en_US' : current($localeCodes);
        $locale       = current($this->getLocaleManager()->getLocales(array('code' => $localeCode)));
        $localeAttr   = $this->findAttribute('cataloglocale');
        $localeOption = $this->findAttributeOptionWithValue($localeAttr, $locale->getCode());

        $scope       = current($this->getChannelManager()->getChannels());
        $scopeAttr   = $this->findAttribute('catalogscope');
        $scopeOption = $this->findAttributeOptionWithValue($scopeAttr, $scope->getCode());

        $tree       = current($this->getCategoryManager()->getTrees());
        $treeAttr   = $this->findAttribute('defaulttree');
        $treeOption = $this->findAttributeOptionWithValue($treeAttr, $tree->getCode());

        foreach ($users as $user) {
            $user->setCataloglocale($localeOption);
            $user->setCatalogscope($scopeOption);
            $user->setDefaulttree($treeOption);
            $this->persist($user);
        }

        $this->flush();
    }

    /**
     * Get locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->container->get('pim_catalog.manager.locale');
    }

    /**
     * Get channel manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ChannelManager
     */
    protected function getChannelManager()
    {
        return $this->container->get('pim_catalog.manager.channel');
    }

    /**
     * Get category manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->container->get('pim_catalog.manager.category');
    }

    /**
     * Finds an attribute
     *
     * @param string $attributeCode
     *
     * @return AbstractAttribute
     */
    protected function findAttribute($attributeCode)
    {
        return $this->userRepository->findAttributeByCode($attributeCode);
    }

    /**
     * Finds an attribute option with value
     *
     * @param AbstractAttribute $attribute
     * @param string            $value
     *
     * @return AbstractAttributeOption
     * @throws \LogicException
     */
    protected function findAttributeOptionWithValue(AbstractAttribute $attribute, $value)
    {
        /** @var $options \Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
        $options = $this->userManager->getAttributeOptionRepository()->findBy(
            array('attribute' => $attribute)
        );

        foreach ($options as $option) {
            if ($value == $option->getOptionValue()->getValue()) {
                return $option;
            }
        }

        throw new \LogicException(sprintf('Cannot find attribute option with value "%s"', $value));
    }

    /**
     * Persist object
     *
     * @param mixed $object
     */
    protected function persist($object)
    {
        $this->userManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     */
    protected function flush()
    {
        $this->userManager->getStorageManager()->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 112;
    }
}
