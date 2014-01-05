<?php

namespace Pim\Bundle\FlexibleEntityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Pim\Bundle\FlexibleEntityBundle\FlexibleEntityEvents;
use Pim\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent;
use Pim\Bundle\FlexibleEntityBundle\Event\FilterFlexibleValueEvent;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Flexible object manager, allow to use flexible entity in storage agnostic way
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleManager implements TranslatableInterface, ScopableInterface
{
    /**
     * @var string
     */
    protected $flexibleName;

    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $flexibleRepository;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Locale code (from config or choose by user)
     * @var string
     */
    protected $locale;

    /**
     * Scope code (from config or choose by user)
     * @var string
     */
    protected $scope;

    /**
     * Constructor
     *
     * @param string                   $flexibleName    Entity name
     * @param ObjectManager            $manager         Object manager
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    public function __construct($flexibleName, ObjectManager $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->flexibleName         = $flexibleName;
        $this->objectManager        = $manager;
        $this->eventDispatcher      = $eventDispatcher;

        // TODO : use a configuration object
        $this->flexibleConfig       = array();
        $entityMeta      = $this->objectManager->getClassMetadata($this->flexibleName);
        $valueClass      = $entityMeta->getAssociationMappings()['values']['targetEntity'];
        $valueMeta       = $this->objectManager->getClassMetadata($valueClass);
        $attributeClass  = $valueMeta->getAssociationMappings()['attribute']['targetEntity'];
        $attributeMeta   = $this->objectManager->getClassMetadata($attributeClass);
        $optionClass     = $attributeMeta->getAssociationMappings()['options']['targetEntity'];
        $optionMeta      = $this->objectManager->getClassMetadata($optionClass);
        $optionValClass  = $optionMeta->getAssociationMappings()['optionValues']['targetEntity'];

        $this->flexibleConfig = array(
            'flexible_class'               => $flexibleName,
            'flexible_value_class'         => $valueClass,
            'attribute_class'              => $attributeClass,
            'attribute_option_class'       => $optionClass,
            'attribute_option_value_class' => $optionValClass
        );

        $this->flexibleRepository   = $manager->getRepository($this->flexibleName);
        $this->flexibleRepository->setFlexibleConfig($this->flexibleConfig);
    }

    /**
     * Get flexible entity config
     *
     * @return array
     */
    public function getFlexibleConfig()
    {
        return $this->flexibleConfig;
    }

    /**
     * Get flexible init mode
     *
     * @return array
     */
    public function getFlexibleInitMode()
    {
        return 'required_attributes';
    }

    /**
     * Return asked locale code or default one
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale code, to force it
     *
     * @param string $code
     *
     * @return FlexibleManager
     */
    public function setLocale($code)
    {
        $this->locale = $code;
        $this->flexibleRepository->setLocale($code);

        return $this;
    }

    /**
     * Return asked scope code or default one
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set scope code, to force it
     *
     * @param string $code
     *
     * @return FlexibleManager
     */
    public function setScope($code)
    {
        $this->scope = $code;
        $this->flexibleRepository->setScope($code);

        return $this;
    }

    /**
     * Get object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Return implementation class that can be use to instanciate
     *
     * @return string
     */
    public function getFlexibleName()
    {
        return $this->flexibleName;
    }

    /**
     * Return class name that can be used to get the repository or instance
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->flexibleConfig['attribute_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     *
     * @return string
     */
    public function getAttributeOptionName()
    {
        return $this->flexibleConfig['attribute_option_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     *
     * @return string
     */
    public function getAttributeOptionValueName()
    {
        return $this->flexibleConfig['attribute_option_value_class'];
    }

    /**
     * Return class name that can be used to get the repository or instance
     *
     * @return string
     */
    public function getFlexibleValueName()
    {
        return $this->flexibleConfig['flexible_value_class'];
    }

    /**
     * Return related repository
     *
     * @return FlexibleEntityRepository
     */
    public function getFlexibleRepository()
    {
        return $this->flexibleRepository;
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getAttributeRepository()
    {
        return $this->objectManager->getRepository($this->getAttributeName());
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->objectManager->getRepository($this->getAttributeOptionName());
    }

    /**
     * Return a new instance
     *
     * @return FlexibleInterface
     */
    public function createFlexible()
    {
        $class = $this->getFlexibleName();
        $attributeClass = $this->getAttributeName();
        $valueClass = $this->getFlexibleValueName();

        $flexible = new $class();
        $flexible->setLocale($this->getLocale());
        $flexible->setScope($this->getScope());

        $codeToAttributeData = $this->getObjectManager()->getRepository($attributeClass)->getCodeToAttributes($class);
        $flexible->setAllAttributes($codeToAttributeData);
        $flexible->setValueClass($valueClass);

        $event = new FilterFlexibleEvent($this, $flexible);
        $this->eventDispatcher->dispatch(FlexibleEntityEvents::CREATE_FLEXIBLE, $event);

        return $flexible;
    }

    /**
     * Return a new instance
     *
     * @return FlexibleValueInterface
     */
    public function createFlexibleValue()
    {
        $class = $this->getFlexibleValueName();
        $value = new $class();
        // dispatch event
        $event = new FilterFlexibleValueEvent($this, $value);
        $this->eventDispatcher->dispatch(FlexibleEntityEvents::CREATE_VALUE, $event);

        return $value;
    }
}
