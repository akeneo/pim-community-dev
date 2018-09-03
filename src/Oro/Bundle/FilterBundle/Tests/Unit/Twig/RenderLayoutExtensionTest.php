<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Twig;

use Oro\Bundle\FilterBundle\Twig\RenderLayoutExtension;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormView;

class RenderLayoutExtensionTest extends AbstractExtensionTestCase
{
    /**
     * Testing class name
     */
    const TESTING_CLASS = RenderLayoutExtension::class;

    /**#@+
     * Test parameters
     */
    const TEST_FIRST_EXISTING_TYPE = 'test_first_existing_type';
    const TEST_SECOND_EXISTING_TYPE = 'test_second_existing_type';
    /**#@-*/

    /**
     * @var RenderLayoutExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = [
        'oro_filter_render_filter_javascript' => [
            'callback'          => 'renderFilterJavascript',
            'safe'              => ['html'],
            'needs_environment' => true
        ],
    ];

    /**
     * @var array
     */
    protected $expectedFilters = [
        'oro_filter_choices' => [
            'callback' => 'getChoices'
        ]
    ];

    /**
     * Data provider for testRenderFilterJs
     *
     * @return array
     */
    public function renderFilterJavascriptDataProvider()
    {
        return [
            'empty_prefixes' => [
                '$blockPrefixes' => []
            ],
            'incorrect_prefixes' => [
                '$blockPrefixes' => 'not_array_data'
            ],
            'no_existing_block' => [
                '$blockPrefixes' => [
                    'not',
                    'existing',
                    'blocks'
                ]
            ],
            'existing_blocks' => [
                '$blockPrefixes' => [
                    'some',
                    self::TEST_FIRST_EXISTING_TYPE,
                    'existing',
                    self::TEST_SECOND_EXISTING_TYPE,
                    'blocks'
                ],
                '$expectedBlock' => self::TEST_SECOND_EXISTING_TYPE . RenderLayoutExtension::SUFFIX
            ],
        ];
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
        $formView->vars = ['block_prefixes' => $blockPrefixes];

        $template = $this->getMockForAbstractClass(
            '\Twig_Template',
            [],
            '',
            false,
            true,
            true,
            ['hasBlock', 'renderBlock']
        );
        $template->expects($this->any())
            ->method('hasBlock')
            ->will($this->returnCallback([$this, 'hasBlockCallback']));
        if ($expectedBlock) {
            $template->expects($this->once())
                ->method('renderBlock')
                ->with($expectedBlock, ['formView' => $formView])
                ->will($this->returnValue(self::TEST_BLOCK_HTML));
        }

        $environment = $this->createMock('\Twig_Environment', ['loadTemplate']);
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
        $existingBlocks = [
            self::TEST_FIRST_EXISTING_TYPE . RenderLayoutExtension::SUFFIX,
            self::TEST_SECOND_EXISTING_TYPE . RenderLayoutExtension::SUFFIX
        ];

        return in_array($blockName, $existingBlocks);
    }

    public function testGetChoices()
    {
        $actualData = [
            new ChoiceView('data_1', 'value_1', 'label_1'),
            new ChoiceView('data_2', 'value_2', 'label_2'),
            'additional' => 'choices',
        ];
        $expectedData = [
            ['value'  => 'value_1', 'label' => 'label_1'],
            ['value'  => 'value_2', 'label' => 'label_2'],
        ];

        $this->assertEquals($expectedData, $this->extension->getChoices($actualData));
    }
}
