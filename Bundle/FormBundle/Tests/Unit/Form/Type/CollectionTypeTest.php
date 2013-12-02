<?php
namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormView;
use Oro\Bundle\FormBundle\Form\Type\CollectionType;

class CollectionTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new CollectionType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMock('Symfony\Component\Form\Test\FormBuilderInterface');

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\FormBundle\Form\EventListener\CollectionTypeSubscriber'));

        $options = array();
        $this->type->buildForm($builder, $options);
    }

    public function testBuildView()
    {
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $view = new FormView();

        $options = array(
            'show_form_when_empty' => true
        );
        $this->type->buildView($view, $form, $options);

        $this->assertArrayHasKey('show_form_when_empty', $view->vars);
        $this->assertEquals($options['show_form_when_empty'], $view->vars['show_form_when_empty']);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('collection', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_collection', $this->type->getName());
    }
}
