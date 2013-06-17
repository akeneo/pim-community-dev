<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

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
        $this->userManager = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // change existing users (imported by user bundle itself)
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $this->setFlexibleAttributeValueOption($user, 'cataloglocale', 'en_US');
            $this->setFlexibleAttributeValueOption($user, 'catalogscope', 'mobile');
            $this->persist($user);
        }

        $this->flush();
    }

    /**
     * Sets a flexible attribute value as option with given value
     *
     * @param AbstractFlexible $flexibleEntity
     * @param string           $attributeCode
     * @param string           $value
     *
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValueOption(AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
        if ($attribute = $this->findAttribute($attributeCode)) {
            $option = $this->findAttributeOptionWithValue($attribute, $value);
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->setOption($option);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Finds an attribute
     *
     * @param string $attributeCode
     *
     * @return AbstractAttribute
     */
    private function findAttribute($attributeCode)
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
    private function findAttributeOptionWithValue(AbstractAttribute $attribute, $value)
    {
        /** @var $options \Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
        $options = $this->userManager->getAttributeOptionRepository()->findBy(
            array('attribute' => $attribute)
        );

        $selectedOption = null;
        foreach ($options as $option) {
            if ($value == $option->getOptionValue()->getValue()) {
                return $option;
            }
        }

        throw new \LogicException(sprintf('Cannot find attribute option with value "%s"', $value));
    }

    /**
     * Gets or creates a flexible value for attribute
     *
     * @param AbstractFlexible  $flexibleEntity
     * @param AbstractAttribute $attribute
     *
     * @return FlexibleValueInterface
     */
    private function getFlexibleValueForAttribute(AbstractFlexible $flexibleEntity, AbstractAttribute $attribute)
    {
        $flexibleValue = $flexibleEntity->getValue($attribute->getCode());
        if (!$flexibleValue) {
            $flexibleValue = $this->userManager->createFlexibleValue();
            $flexibleValue->setAttribute($attribute);
            $flexibleEntity->addValue($flexibleValue);
        }

        return $flexibleValue;
    }

    /**
     * Persist object
     *
     * @param mixed $object
     *
     * @return void
     */
    private function persist($object)
    {
        $this->userManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @return void
     */
    private function flush()
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
