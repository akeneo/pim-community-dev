<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\UserBundle\Form\Type\UserSelectType;

class UserSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserSelectType
     */
    protected $type;

    /**
     * @var EntityTransformerInterface
     */
    protected $transformer;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->transformer = $this->getMockBuilder('Oro\Bundle\FormBundle\EntityAutocomplete\Transformer\EntityTransformerInterface')
            ->getMock();
        $this->type = new UserSelectType($this->transformer);
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_jqueryselect2_hidden', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_user_select', $this->type->getName());
    }
}
