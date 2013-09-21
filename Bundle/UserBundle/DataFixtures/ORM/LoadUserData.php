<?php
namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\UserManager;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $this->loadAttributes($userManager);

        $admin = $userManager->createUser();
        $api = new UserApi();

        $api->setApiKey('admin_api_key')
            ->setUser($admin);

        $admin
            ->setUsername('admin')
            ->setPlainPassword('admin')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->addRole($this->getReference('admin_role'))
            ->addGroup($this->getReference('oro_group_administrators'))
            ->setEmail('admin@example.com')
            ->setApi($api);
        if ($this->hasReference('default_business_unit')) {
            $admin->setOwner($this->getReference('default_business_unit'));
        }
        $this->setFlexibleAttributeValueOption($userManager, $admin, 'gender', 0);
        $this->setFlexibleAttributeValue($userManager, $admin, 'company', '');
        $this->addReference('default_user', $admin);
        $userManager->updateUser($admin);
    }

    /**
     * Load attributes
     *
     * @param UserManager $entityManager
     * @return void
     */
    public function loadAttributes($entityManager)
    {
        $this->assertHasRequiredAttributes($entityManager, array('company', 'gender'));

    }

    /**
     * Asserts required attributes were created
     *
     * @param UserManager $entityManager
     * @param array $attributeCodes
     * @throws \LogicException
     */
    private function assertHasRequiredAttributes($entityManager, $attributeCodes)
    {
        foreach ($attributeCodes as $attributeCode) {
            if (!$this->findAttribute($entityManager, $attributeCode)) {
                throw new \LogicException(
                    sprintf(
                        'Attribute "%s" is missing, please load "%s" fixture before',
                        $attributeCode,
                        'Acme\Bundle\DemoBundle\DataFixtures\ORM\LoadUserAttrData'
                    )
                );
            }
        }
    }

    /**
     * Finds an attribute
     *
     * @param UserManager $entityManager
     * @param string $attributeCode
     * @return AbstractAttribute
     */
    private function findAttribute($entityManager, $attributeCode)
    {
        return $entityManager->getRepository()->findAttributeByCode($attributeCode);
    }

    /**
     * Sets a flexible attribute value as option with given value
     *
     * @param UserManager $entityManager
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValueOption(
        $entityManager,
        AbstractFlexible $flexibleEntity,
        $attributeCode,
        $value
    ) {
        if ($attribute = $this->findAttribute($entityManager, $attributeCode)) {
            $option = $this->findAttributeOptionWithValue($entityManager, $attribute, $value);
            $this->getFlexibleValueForAttribute($entityManager, $flexibleEntity, $attribute)->setOption($option);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Sets a flexible attribute value
     *
     * @param UserManager $entityManager
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValue($entityManager, AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
        if ($attribute = $this->findAttribute($entityManager, $attributeCode)) {
            $this->getFlexibleValueForAttribute($entityManager, $flexibleEntity, $attribute)->setData($value);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Finds an attribute option with value
     *
     * @param UserManager $entityManager
     * @param AbstractAttribute $attribute
     * @param string $value
     * @return AbstractAttributeOption
     * @throws \LogicException
     */
    private function findAttributeOptionWithValue($entityManager, AbstractAttribute $attribute, $value)
    {
        /** @var $options \Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
        $options = $entityManager->getAttributeOptionRepository()->findBy(
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
     * @param UserManager $entityManager
     * @param AbstractFlexible $flexibleEntity
     * @param AbstractAttribute $attribute
     * @return FlexibleValueInterface
     */
    private function getFlexibleValueForAttribute(
        $entityManager,
        AbstractFlexible $flexibleEntity,
        AbstractAttribute $attribute
    ) {
        $flexibleValue = $flexibleEntity->getValue($attribute->getCode());
        if (!$flexibleValue) {
            $flexibleValue = $entityManager->createFlexibleValue();
            $flexibleValue->setAttribute($attribute);
            $flexibleEntity->addValue($flexibleValue);
        }
        return $flexibleValue;
    }

    /**
     * Persist object
     *
     * @param UserManager $entityManager
     * @param mixed $object
     * @return void
     */
    private function persist($entityManager, $object)
    {
        $entityManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @param UserManager $entityManager
     * @return void
     */
    private function flush($entityManager)
    {
        $entityManager->getStorageManager()->flush();
    }

    public function getOrder()
    {
        return 110;
    }
}
