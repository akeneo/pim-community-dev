<?php

namespace Pim\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load required user data for PIM
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadUserAttrData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        // $this->userManager    = $container->get('oro_user.manager');
        // $this->userRepository = $this->userManager->getFlexibleRepository();
    }

    /**
     * Load sample user group data
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        return ;

        $storageManager = $this->userManager->getStorageManager();

        $attribute = $this->createAttributeWithOptions(
            'pim_flexibleentity_simpleselect',
            'cataloglocale',
            $this->getLocales(),
            true,
            'Catalog locale'
        );
        $storageManager->persist($attribute);

        $attribute = $this->createAttributeWithOptions(
            'pim_flexibleentity_simpleselect',
            'catalogscope',
            $this->getScopes(),
            true,
            'Catalog scope'
        );
        $storageManager->persist($attribute);

        $attribute = $this->createAttributeWithOptions(
            'pim_flexibleentity_simpleselect',
            'defaulttree',
            $this->getTrees(),
            true,
            'Default tree'
        );
        $storageManager->persist($attribute);

        $storageManager->flush();
    }

    /**
     * Create an attribute
     *
     * @param string $attributeType
     * @param string $attributeCode
     *
     * @return AbstractAttribute
     */
    protected function createAttribute($attributeType, $attributeCode)
    {
        $result = $this->userManager->createAttribute($attributeType);
        $result->setCode($attributeCode);
        $result->setLabel($attributeCode);

        return $result;
    }

    /**
     * Create an attribute option with value
     *
     * @param string $value
     *
     * @return AbstractAttributeOption
     */
    protected function createAttributeOptionWithValue($value)
    {
        $option = $this->userManager->createAttributeOption();
        $optionValue = $this->userManager->createAttributeOptionValue()->setValue($value);
        $option->addOptionValue($optionValue);

        return $option;
    }

    /**
     * Create an attribute with options
     *
     * @param string  $attributeType
     * @param string  $attributeCode
     * @param array   $optionValues
     * @param boolean $required
     * @param mixed   $label
     *
     * @return AbstractAttribute
     */
    protected function createAttributeWithOptions(
        $attributeType,
        $attributeCode,
        array $optionValues,
        $required = false,
        $label = false
    ) {
        $attribute = $this->createAttribute($attributeType, $attributeCode);
        foreach ($optionValues as $value) {
            $attribute->addOption($this->createAttributeOptionWithValue($value));
            $attribute->setRequired($required);
            if ($label) {
                $attribute->setLabel($label);
            }
        }

        return $attribute;
    }

    /**
     * Get array of locales
     *
     * @return array
     */
    protected function getLocales()
    {
        return $this->getLocaleManager()->getActiveCodes();
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
     * Get array of scopes
     *
     * @return array
     */
    protected function getScopes()
    {
        $channels = $this->getChannelManager()->getChannels();

        $choices = array();
        foreach ($channels as $channel) {
            $choices[] = $channel->getCode();
        }

        return $choices;
    }

    /**
     * Get array of category trees
     *
     * @return array
     */
    protected function getTrees()
    {
        $trees = $this->container->get('pim_catalog.manager.category')->getTrees();

        $choices = array();
        foreach ($trees as $tree) {
            $choices[] = $tree->getCode();
        }

        return $choices;
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
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 111;
    }
}
