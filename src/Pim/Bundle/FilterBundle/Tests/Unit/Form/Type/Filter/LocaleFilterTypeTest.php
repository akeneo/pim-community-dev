<?php

namespace Pim\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Pim\Bundle\FilterBundle\Form\Type\Filter\LocaleFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter\ChoiceFilterTypeTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleFilterTypeTest extends ChoiceFilterTypeTest
{

    /**
     * @var ScopeFilterType
     */
    protected $type;

    /**
     * @staticvar array
     */
    protected static $localeChoices = array(
        'en_US' => 'en_US',
        'fr_FR' => 'fr_FR',
        'en_GB' => 'en_GB'
    );

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $translator = $this->createMockTranslator();
        $localeManager = $this->createMockLocaleManager();

        $this->type = new LocaleFilterType($translator, $localeManager);
        $this->factory->addType(new FilterType($translator));
        $this->factory->addType(new ChoiceFilterType($translator));
    }

    /**
     * Create mock locale manager
     *
     * @return Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected function createMockLocaleManager()
    {
        $localeManager = $this->getMockBuilder('Pim\Bundle\ConfigBundle\Manager\LocaleManager')
                              ->disableOriginalConstructor()
                              ->getMock();

        $localeManager->expects($this->any())
                      ->method('getActiveCodesWithUserLocale')
                      ->will($this->returnValue(self::$localeChoices));

        return $localeManager;
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
        $this->assertEquals(LocaleFilterType::NAME, $this->type->getName());
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
                    'field_options' => array('choices' => self::$localeChoices)
                )
            )
        );
    }
}
