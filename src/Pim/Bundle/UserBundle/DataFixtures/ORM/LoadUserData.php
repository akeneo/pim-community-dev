<?php
namespace Pim\Bundle\UserBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;

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
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container      = $container;
        $this->userManager    = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $users = $this->userRepository->findAll();

        $locale       = current($this->getLocaleManager()->getLocales(array('code' => 'en_US')));
        $localeAttr   = $this->findAttribute('cataloglocale');
        $localeOption = $this->findAttributeOptionWithValue($localeAttr, $locale->getCode());

        $scope       = current($this->getChannelManager()->getChannels());
        $scopeAttr   = $this->findAttribute('catalogscope');
        $scopeOption = $this->findAttributeOptionWithValue($scopeAttr, $scope->getCode());

        foreach ($users as $user) {
            $user->setCataloglocale($localeOption);
            $user->setCatalogscope($scopeOption);
            $this->persist($user);
        }

        $this->flush();
    }

    /**
     * Get locale manager
     *
     * @return \Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->container->get('pim_config.manager.locale');
    }

    /**
     * Get channel manager
     *
     * @return \Pim\Bundle\ConfigBundle\Manager\ChannelManager
     */
    protected function getChannelManager()
    {
        return $this->container->get('pim_config.manager.channel');
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
        /** @var $options \Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
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
     *
     * @return void
     */
    protected function persist($object)
    {
        $this->userManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @return void
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
