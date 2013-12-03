<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\TextType;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Container;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;

/**
 * Test related class
 */
abstract class AbstractFlexibleManagerTest extends AbstractOrmTest
{

    /**
     * @var FlexibleManager
     */
    protected $manager;

    /**
     * @var AttributeTypeFactory
     */
    protected $attributeTypeFactory;

    /**
     * @var string
     */
    protected $attributeClassName;

    /**
     * @var string
     */
    protected $attributeOptionClassName;

    /**
     * @var string
     */
    protected $attributeOptionValueClassName;

    /**
     * @var string
     */
    protected $flexibleClassName;

    /**
     * @var string
     */
    protected $flexibleValueClassName;

    /**
     * @var string
     */
    protected $defaultScope;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        parent::setUp();
        // flexible entity manager configuration
        $this->attributeClassName            = 'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute';
        $this->attributeOptionClassName      = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption';
        $this->attributeOptionValueClassName = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue';
        $this->flexibleClassName             = 'Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible';
        $this->flexibleValueClassName        = 'Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue';
        $this->defaultLocale                 = 'en';
        $this->defaultScope                  = 'mobile';
        $this->flexibleConfig = array(
            'entities_config' => array(
                $this->flexibleClassName => array(
                    'flexible_manager'             => 'demo_manager',
                    'flexible_class'               => $this->flexibleClassName,
                    'flexible_value_class'         => $this->flexibleValueClassName,
                    'attribute_class'              => $this->attributeClassName,
                    'attribute_option_class'       => $this->attributeOptionClassName,
                    'attribute_option_value_class' => $this->attributeOptionValueClassName,
                    'default_locale'               => $this->defaultLocale,
                    'default_scope'                => $this->defaultScope
                )
            )
        );
        // mock global event dispatcher 'event_dispatcher'
        $dispatcher = new EventDispatcher();

        // prepare test container
        $this->container->setParameter('oro_flexibleentity.flexible_config', $this->flexibleConfig);

        // prepare attribute type factory
        $attType = new TextType(
            'varchar',
            'text',
            $this->getMock('Oro\Bundle\FlexibleEntityBundle\Form\Validator\AttributeConstraintGuesser')
        );
        $this->container->set('oro_flexibleentity.attributetype.text', $attType);
        $attTypes = array('oro_flexibleentity_text' => 'oro_flexibleentity.attributetype.text');
        $this->attributeTypeFactory = new AttributeTypeFactory($this->container, $attTypes);

        // prepare simple entity manager (use default entity manager)
        $this->manager = new FlexibleManager(
            $this->flexibleClassName,
            $this->flexibleConfig,
            $this->entityManager,
            $dispatcher,
            $this->attributeTypeFactory
        );

        // add attribute types
        foreach ($attTypes as $attTypeAlias => $attTypeId) {
            $this->manager->addAttributeType($attTypeAlias);
        }

        $this->container->set('demo_manager', $this->manager);
        $this->container->set('event_dispatcher', $dispatcher);
    }
}
