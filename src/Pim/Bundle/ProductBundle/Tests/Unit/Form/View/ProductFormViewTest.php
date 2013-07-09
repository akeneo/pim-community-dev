<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\View;

use Pim\Bundle\ProductBundle\Form\View\ProductFormView;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFormViewTest extends \PHPUnit_Framework_TestCase
{
    protected $formView = null;

    public function setUp()
    {
        $this->formView = new ProductFormView;
    }

    public function testAddChildrenWithBasicValue()
    {
        $group = $this->getGroupMock(array(
            'id'   => 1,
            'name' => 'General',
        ));

        $attribute = $this->getAttributeMock(array(
            'id'           => 42,
            'virtualGroup' => $group,
            'code'         => 'name',
            'label'        => 'Name',
            'sortOrder'    => 0,
            'scopable'     => false,
        ));

        $value = $this->getValueMock(array(
            'attribute' => $attribute,
            'removable' => true,
        ));

        $view = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($value, $view);

        $formView = array(
            1 => array(
                'name'       => 'General',
                'attributes' => array(
                    42 => array(
                        'isRemovable' => true,
                        'code'        => 'name',
                        'label'       => 'Name',
                        'sortOrder'   => 0,
                        'value'       => $view,
                    ),
                ),
            ),
        );

        $this->assertEquals($formView, $this->formView->getView());
    }

    public function testAddChildrenWithScopableValue()
    {
        $group = $this->getGroupMock(array(
            'id'   => 1,
            'name' => 'General',
        ));

        $attribute = $this->getAttributeMock(array(
            'id'           => 42,
            'virtualGroup' => $group,
            'code'         => 'name',
            'label'        => 'Name',
            'sortOrder'    => 0,
            'scopable'     => true,
        ));

        $valueWeb = $this->getValueMock(array(
            'scope'     => 'Web',
            'attribute' => $attribute,
            'removable' => true,
        ));

        $valueMobile = $this->getValueMock(array(
            'scope'     => 'Mobile',
            'attribute' => $attribute,
            'removable' => true,
        ));

        $viewWeb = $this->getMock('Symfony\Component\Form\FormView');
        $viewMobile = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($valueWeb, $viewWeb);
        $this->formView->addChildren($valueMobile, $viewMobile);

        $formView = array(
            1 => array(
                'name'       => 'General',
                'attributes' => array(
                    42 => array(
                        'isRemovable' => true,
                        'code'        => 'name',
                        'label'       => 'Name',
                        'sortOrder'   => 0,
                        'classes'     => array(
                            'scopable' => true
                        ),
                        'values'      => array(
                            'Web'    => $viewWeb,
                            'Mobile' => $viewMobile,
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($formView, $this->formView->getView());
    }

    public function testAddChildrenWithPriceValue()
    {
        $group = $this->getGroupMock(array(
            'id'   => 1,
            'name' => 'General',
        ));

        $attribute = $this->getAttributeMock(array(
            'id'            => 42,
            'virtualGroup'  => $group,
            'code'          => 'name',
            'label'         => 'Name',
            'sortOrder'     => 0,
            'scopable'      => false,
            'attributeType' => 'pim_product_price_collection'
        ));

        $value = $this->getValueMock(array(
            'attribute' => $attribute,
            'removable' => false,
        ));

        $view = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($value, $view);

        $formView = array(
            1 => array(
                'name'       => 'General',
                'attributes' => array(
                    42 => array(
                        'isRemovable' => false,
                        'code'        => 'name',
                        'label'       => 'Name',
                        'sortOrder'   => 0,
                        'classes'     => array(
                            'currency' => true
                        ),
                        'value'       => $view,
                    ),
                ),
            ),
        );

        $this->assertEquals($formView, $this->formView->getView());
    }

    private function getValueMock(array $options)
    {
        $options = array_merge(array(
            'attribute' => null,
            'removable' => null,
            'scope'     => null,
        ), $options);

        $value = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($options['attribute']));

        $value->expects($this->any())
            ->method('isRemovable')
            ->will($this->returnValue($options['removable']));

        $value->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue($options['scope']));

        return $value;
    }

    private function getAttributeMock(array $options)
    {
        $options = array_merge(array(
            'id'            => null,
            'virtualGroup'  => null,
            'code'          => null,
            'label'         => null,
            'sortOrder'     => null,
            'scopable'      => null,
            'attributeType' => null,
        ), $options);

        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');


        $attribute->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($options['id']));

        $attribute->expects($this->any())
            ->method('getVirtualGroup')
            ->will($this->returnValue($options['virtualGroup']));

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($options['code']));

        $attribute->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue($options['label']));

        $attribute->expects($this->any())
            ->method('getSortOrder')
            ->will($this->returnValue($options['sortOrder']));

        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue($options['scopable']));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($options['attributeType']));

        return $attribute;
    }

    private function getGroupMock(array $options)
    {
        $options = array_merge(array(
            'id'   => null,
            'name' => null,
        ), $options);

        $group = $this->getMock('Pim\Bundle\ProductBundle\Entity\AttributeGroup');

        $group->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($options['id']));

        $group->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($options['name']));

        return $group;
    }
}

