<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\TextType;

use Symfony\Component\DependencyInjection\Container;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFlexibleManagerTest extends AbstractOrmTest
{
    /**
     * @var FlexibleManager
     */
    protected $manager;

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
        $this->attributeClassName            = 'Pim\Bundle\FlexibleEntityBundle\Entity\Attribute';
        $this->attributeOptionClassName      = 'Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption';
        $this->attributeOptionValueClassName = 'Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue';
        $this->flexibleClassName             = 'Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible';
        $this->flexibleValueClassName        = 'Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue';
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
        $this->container->setParameter('pim_flexibleentity.flexible_config', $this->flexibleConfig);

        // prepare attribute type factory
        $attType = new TextType(
            'varchar',
            'text',
            $this->getMock('Pim\Bundle\FlexibleEntityBundle\Form\Validator\AttributeConstraintGuesser')
        );
        $this->container->set('pim_flexibleentity.attributetype.text', $attType);

        // prepare simple entity manager (use default entity manager)
        $this->manager = new FlexibleManager(
            $this->flexibleClassName,
            $this->flexibleConfig,
            $this->entityManager,
            $dispatcher
        );

        $this->container->set('demo_manager', $this->manager);
        $this->container->set('event_dispatcher', $dispatcher);
    }
}
