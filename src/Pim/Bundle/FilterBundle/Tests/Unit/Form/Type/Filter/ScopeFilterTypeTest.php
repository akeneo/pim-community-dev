<?php

namespace Pim\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Pim\Bundle\FilterBundle\Form\Type\Filter\ScopeFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter\ChoiceFilterTypeTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ScopeFilterTypeTest extends ChoiceFilterTypeTest
{
    /**
     * @var ScopeFilterType
     */
    protected $type;

    /**
     * @staticvar array
     */
    protected static $channelChoices = array(
        'ecommerce' => 'E-Commerce',
        'mobile'    => 'Mobile'
    );

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markTestSkipped(
            'Due to Symfony 2.3 Upgrade, cf https://github.com/symfony/symfony/blob/master/UPGRADE-2.1.md'
        );
        parent::setUp();

        $translator = $this->createMockTranslator();
        $channelManager = $this->createMockChannelManager();

        $this->type = new ScopeFilterType($translator, $channelManager);
        $this->factory->addType(new FilterType($translator));
        $this->factory->addType(new ChoiceFilterType($translator));
    }

    /**
     * Create mock channel manager
     *
     * @return Pim\Bundle\ProductBundle\Manager\ChannelManager
     */
    protected function createMockChannelManager()
    {
        $channelManager = $this->getMockBuilder('Pim\Bundle\ProductBundle\Manager\ChannelManager')
                               ->disableOriginalConstructor()
                               ->getMock();

        $channelManager->expects($this->any())
                       ->method('getChannelChoiceWithUserChannel')
                       ->will($this->returnValue(self::$channelChoices));

        return $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals(ScopeFilterType::NAME, $this->type->getName());
        $this->assertEquals(ChoiceFilterType::NAME, $this->type->getParent());
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'choice',
                    'field_options' => array('choices' => self::$channelChoices)
                )
            )
        );
    }
}
