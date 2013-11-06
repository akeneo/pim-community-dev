<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Twig;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Oro\Bundle\FilterBundle\Twig\RenderLayoutExtension;

class RenderLayoutExtensionTest extends AbstractExtensionTestCase
{
    /**
     * Testing class name
     */
    const TESTING_CLASS = 'Oro\Bundle\FilterBundle\Twig\RenderLayoutExtension';

    /**#@+
     * Test parameters
     */
    const TEST_FIRST_EXISTING_TYPE  = 'test_first_existing_type';
    const TEST_SECOND_EXISTING_TYPE = 'test_second_existing_type';
    /**#@-*/

    /**
     * @var RenderLayoutExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = array(
        'oro_filter_render_filter_javascript' => array(
            'callback'          => 'renderFilterJavascript',
            'safe'              => array('html'),
            'needs_environment' => true
        ),
    );

    /**
     * @var array
     */
    protected $expectedFilters = array(
        'oro_filter_choices' => array(
            'callback' => 'getChoices'
        )
    );

    /**
     * Data provider for testRenderFilterJs
     *
     * @return array
     */
    public function renderFilterJavascriptDataProvider()
    {
        return array(
            'empty_prefixes' => array(
                '$blockPrefixes' => array()
            ),
            'incorrect_prefixes' => array(
                '$blockPrefixes' => 'not_array_data'
            ),
            'no_existing_block' => array(
                '$blockPrefixes' => array(
                    'not',
                    'existing',
                    'blocks'
                )
            ),
            'existing_blocks' => array(
                '$blockPrefixes' => array(
                    'some',
                    self::TEST_FIRST_EXISTING_TYPE,
                    'existing',
                    self::TEST_SECOND_EXISTING_TYPE,
                    'blocks'
                ),
                '$expectedBlock' => self::TEST_SECOND_EXISTING_TYPE . RenderLayoutExtension::SUFFIX
            ),
        );
    }

    /**
     * @param array $blockPrefixes
     * @param string|null $expectedBlock
     *
     * @dataProvider renderFilterJavascriptDataProvider
     */
    public function testRenderFilterJavascript($blockPrefixes, $expectedBlock = null)
    {
        $formView = new FormView();
        $formView->vars = array('block_prefixes' => $blockPrefixes);

        $template = $this->getMockForAbstractClass(
            '\Twig_Template',
            array(),
            '',
            false,
            true,
            true,
            array('hasBlock', 'renderBlock')
        );
        $template->expects($this->any())
            ->method('hasBlock')
            ->will($this->returnCallback(array($this, 'hasBlockCallback')));
        if ($expectedBlock) {
            $template->expects($this->once())
                ->method('renderBlock')
                ->with($expectedBlock, array('formView' => $formView))
                ->will($this->returnValue(self::TEST_BLOCK_HTML));
        }

        $environment = $this->getMock('\Twig_Environment', array('loadTemplate'));
        $environment->expects($this->any())
            ->method('loadTemplate')
            ->with(self::TEST_TEMPLATE_NAME)
            ->will($this->returnValue($template));

        $html = $this->extension->renderFilterJavascript($environment, $formView);
        if ($expectedBlock) {
            $this->assertEquals(self::TEST_BLOCK_HTML, $html);
        } else {
            $this->assertEmpty($html);
        }
    }

    /**
     * Callback for Twig_Template::hasBlock
     *
     * @param string $blockName
     * @return bool
     */
    public function hasBlockCallback($blockName)
    {
        $existingBlocks = array(
            self::TEST_FIRST_EXISTING_TYPE . RenderLayoutExtension::SUFFIX,
            self::TEST_SECOND_EXISTING_TYPE . RenderLayoutExtension::SUFFIX
        );

        return in_array($blockName, $existingBlocks);
    }

    public function testGetChoices()
    {
        $actualData = array(
            new ChoiceView('data_1', 'value_1', 'label_1'),
            new ChoiceView('data_2', 'value_2', 'label_2'),
            'additional' => 'choices',
        );
        $expectedData = [
            ['value'  => 'value_1', 'label' => 'label_1'],
            ['value'  => 'value_2', 'label' => 'label_2'],
        ];

        $this->assertEquals($expectedData, $this->extension->getChoices($actualData));
    }
}
