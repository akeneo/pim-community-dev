<?php

namespace Akeneo\Platform\Bundle\UIBundle\Tests\Unit\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformer;
use Akeneo\Platform\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformerFactory;
use Akeneo\Platform\Bundle\UIBundle\Form\Type\AjaxEntityType;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTypeTest extends TestCase
{
    protected $router;
    protected $transformerFactory;
    protected $transformer;
    protected $userContext;
    protected $type;

    protected $options = [
        'class'                 => 'class',
        'multiple'              => 'multiple',
        'locale'                => 'locale',
        'collection_id'         => 'collection_id',
        'transformer_options'   => [
            'option1' => 'value1',
            'option2' => 'option2'
        ]
    ];

    protected $transformerOptions = [
        'class'                 => 'class',
        'multiple'              => 'multiple',
        'locale'                => 'locale',
        'collection_id'         => 'collection_id',
        'option1'               => 'value1',
        'option2'               => 'option2'
    ];

    /**
     * @{@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->router->expects($this->any())
            ->method('generate')
            ->will(
                $this->returnCallback(
                    function ($route, $parameters) {
                        $route .= '?';
                        foreach ($parameters as $key => $value) {
                            $route .= "&$key=$value";
                        }

                        return $route;
                    }
                )
            );
        $this->transformerFactory = $this->getMockBuilder(
            AjaxEntityTransformerFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = $this->getMockBuilder(AjaxEntityTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformerFactory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function ($transformerOptions) {
                        $this->assertEquals($this->transformerOptions, $transformerOptions);

                        return $this->transformer;
                    }
                )
            );
        $this->userContext = $this->getMockBuilder(UserContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext->expects($this->any())
            ->method('getCurrentLocaleCode')
            ->will($this->returnValue('locale'));
        $this->type = new AjaxEntityType($this->router, $this->transformerFactory, $this->userContext);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder(FormBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->with($this->identicalTo($this->transformer));
        $this->type->buildForm($builder, $this->options);
    }

    public function getSetDefaultOptionsData()
    {
        return [
            'defaults'      => [[]],
            'with_locale'   => [
                ['locale' => 'other_locale'],
                [
                    'url' => 'pim_ui_ajaxentity_list?&class=class&dataLocale=other_locale&collectionId=&isCreatable='
                ]
            ],
            'with_url'      => [['url' => 'url']],
            'with_params'   => [
                [
                    'multiple'              => true,
                    'transformer_options'   => ['key1' => 'val1'],
                    'collection_id'         => 'collection_id',
                    'route'                 => 'route',
                    'route_parameters'      => ['param1' => 'val1'],
                    'minimum_input_length'  => 5,
                ],
                [
                    'url' => 'route?&param1=val1&class=class&dataLocale=locale&collectionId=collection_id&isCreatable='
                ]
            ]
        ];
    }

    /**
     * @dataProvider getSetDefaultOptionsData
     */
    public function testSetDefaultOptions($options, $expected = [])
    {
        $options = $options + ['class' => 'class'];
        $expected = $expected + $options + [
            'multiple'              => false,
            'transformer_options'   => [],
            'collection_id'         => null,
            'route'                 => 'pim_ui_ajaxentity_list',
            'route_parameters'      => [],
            'is_creatable'          => false,
            'data_class'            => null,
            'minimum_input_length'  => 0,
            'error_bubbling'        => false,
            'locale'                => 'locale',
            'url'                   =>
                'pim_ui_ajaxentity_list?&class=class&dataLocale=locale&collectionId=&isCreatable='
            ];
        $resolver = new OptionsResolver();
        $this->type->configureOptions($resolver);
        $this->assertEquals($expected, $resolver->resolve($options));
    }
}
