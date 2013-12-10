<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Tests related class
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxEntityTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $router;
    protected $transformerFactory;
    protected $transformer;
    protected $localeManager;
    protected $locale;
    protected $type;

    protected $options = array(
        'class'                 => 'class',
        'multiple'              => 'multiple',
        'locale'                => 'locale',
        'collection_id'         => 'collection_id',
        'transformer_options'   => array(
            'option1' => 'value1',
            'option2' => 'option2'
        )
    );
    
    protected $transformerOptions = array(
        'class'                 => 'class',
        'multiple'              => 'multiple',
        'locale'                => 'locale',
        'collection_id'         => 'collection_id',
        'option1' => 'value1',
        'option2' => 'option2'
    );


    protected function setUp()
    {
        $this->router = $this->getMock('Symfony\Component\Routing\RouterInterface');
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
            'Pim\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformerFactory'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = $this->getMockBuilder('Pim\Bundle\UIBundle\Form\Transformer\AjaxEntityTransformer')
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
        $this->localeManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->locale = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Locale');
        $this->localeManager->expects($this->any())
            ->method('getDataLocale')
            ->will($this->returnValue($this->locale));
        $this->locale->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('locale'));
        $this->type = new AjaxEntityType($this->router, $this->transformerFactory, $this->localeManager);
    }
    
    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->with($this->identicalTo($this->transformer));
        $this->type->buildForm($builder, $this->options);
    }
    
    public function getSetDefaultOptionsData()
    {
        return array(
            'defaults'      => array(array()),
            'with_locale'   => array(
                array('locale' => 'other_locale'),
                array('url' => 'pim_ui_ajaxentity_list?&class=class&dataLocale=other_locale&collectionId=')
            ),
            'with_url'      => array(array('url' => 'url')),
            'with_params'   => array(
                array(
                    'multiple'              => true,
                    'transformer_options'   => array('key1' => 'val1'),
                    'collection_id'         => 'collection_id',
                    'route'                 => 'route',
                    'route_parameters'      => array('param1' => 'val1'),
                    'minimum_input_length'  => 5,
                ),
                array('url' => 'route?&param1=val1&class=class&dataLocale=locale&collectionId=collection_id')
            )
        );
    }

    /**
     * @dataProvider getSetDefaultOptionsData
     */
    public function testSetDefaultOptions($options, $expected = array())
    {
        $options = $options + array('class' => 'class');
        $expected = $expected + $options + array(
            'multiple'              => false,
            'transformer_options'   => array(),
            'collection_id'         => null,
            'route'                 => 'pim_ui_ajaxentity_list',
            'route_parameters'      => array(),
            'data_class'            => null,
            'minimum_input_length'  => 0,
            'locale'                => 'locale',
            'url'                   => 'pim_ui_ajaxentity_list?&class=class&dataLocale=locale&collectionId='
        );
        $resolver = new OptionsResolver();
        $this->type->setDefaultOptions($resolver);
        $this->assertEquals($expected, $resolver->resolve($options));
    }
}
