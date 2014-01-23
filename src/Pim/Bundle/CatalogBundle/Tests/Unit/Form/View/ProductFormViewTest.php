<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\View;

use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFormViewTest extends \PHPUnit_Framework_TestCase
{
    protected $formView = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formView = new ProductFormView();
    }

    /**
     * Test related method
     */
    public function testAddChildrenWithBasicValue()
    {
        $group = $this->getGroupMock(
            [
                'id'    => 1,
                'label' => 'General',
            ]
        );

        $attribute = $this->getAttributeMock(
            [
                'id'           => 42,
                'virtualGroup' => $group,
                'code'         => 'name',
                'label'        => 'Name',
                'sortOrder'    => 0,
                'scopable'     => false,
            ]
        );

        $value = $this->getValueMock(
            [
                'attribute' => $attribute,
                'removable' => true,
            ]
        );

        $view = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($value, $view);

        $formView = [
            1 => [
                'label'      => 'General',
                'attributes' => [
                    'name_' => [
                        'isRemovable'        => true,
                        'allowValueCreation' => false,
                        'code'               => 'name',
                        'label'              => 'Name',
                        'sortOrder'          => 0,
                        'value'              => $view,
                        'id'                 => 42,
                        'locale'             => null,
                    ],
                ],
            ],
        ];

        $this->assertEquals($formView, $this->formView->getView());
    }

    /**
     * Test related method
     */
    public function testAddMultiChildrenInTheSameGroup()
    {
        $group = $this->getGroupMock(
            [
                'id'    => 1,
                'label' => 'General',
            ]
        );

        $nameAttr = $this->getAttributeMock(
            [
                'id'           => 42,
                'virtualGroup' => $group,
                'code'         => 'name',
                'label'        => 'Name',
                'sortOrder'    => 0,
                'scopable'     => false,
            ]
        );
        $nameValue = $this->getValueMock(
            [
                'attribute' => $nameAttr,
                'removable' => true,
            ]
        );
        $nameView = $this->getMock('Symfony\Component\Form\FormView');

        $colorAttr = $this->getAttributeMock(
            [
                'id'           => 1337,
                'virtualGroup' => $group,
                'code'         => 'color',
                'label'        => 'Color',
                'sortOrder'    => 0,
                'scopable'     => false,
            ]
        );
        $colorValue = $this->getValueMock(
            [
                'attribute' => $colorAttr,
                'removable' => false,
            ]
        );
        $colorView = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($nameValue, $nameView);
        $this->formView->addChildren($colorValue, $colorView);

        $formView = [
            1 => [
                'label'      => 'General',
                'attributes' => [
                    'name_' => [
                        'isRemovable'        => true,
                        'allowValueCreation' => false,
                        'code'               => 'name',
                        'label'              => 'Name',
                        'sortOrder'          => 0,
                        'value'              => $nameView,
                        'id'                 => 42,
                        'locale'             => null,
                    ],
                    'color_' => [
                        'isRemovable'        => false,
                        'allowValueCreation' => false,
                        'code'               => 'color',
                        'label'              => 'Color',
                        'sortOrder'          => 0,
                        'value'              => $colorView,
                        'id'                 => 1337,
                        'locale'             => null,
                    ],
                ],
            ],
        ];

        $this->assertEquals($formView, $this->formView->getView());
    }

    /**
     * Test related method
     */
    public function testAddChildrenWithScopableValue()
    {
        $group = $this->getGroupMock(
            [
                'id'    => 1,
                'label' => 'General',
            ]
        );

        $attribute = $this->getAttributeMock(
            [
                'id'           => 42,
                'virtualGroup' => $group,
                'code'         => 'name',
                'label'        => 'Name',
                'sortOrder'    => 0,
                'scopable'     => true,
            ]
        );

        $valueWeb = $this->getValueMock(
            [
                'scope'     => 'Web',
                'attribute' => $attribute,
                'removable' => true,
            ]
        );

        $valueMobile = $this->getValueMock(
            [
                'scope'     => 'Mobile',
                'attribute' => $attribute,
                'removable' => true,
            ]
        );

        $viewWeb = $this->getMock('Symfony\Component\Form\FormView');
        $viewMobile = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($valueWeb, $viewWeb);
        $this->formView->addChildren($valueMobile, $viewMobile);

        $formView = [
            1 => [
                'label'      => 'General',
                'attributes' => [
                    'name_' => [
                        'isRemovable'        => true,
                        'allowValueCreation' => false,
                        'code'               => 'name',
                        'label'              => 'Name',
                        'sortOrder'          => 0,
                        'id'                 => 42,
                        'locale'             => null,
                        'classes'            => [
                            'scopable' => true
                        ],
                        'values'             => [
                            'Web'    => $viewWeb,
                            'Mobile' => $viewMobile,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($formView, $this->formView->getView());
    }

    /**
     * Test related method
     */
    public function testAddChildrenWithPriceValue()
    {
        $group = $this->getGroupMock(
            [
                'id'    => 1,
                'label' => 'General',
            ]
        );

        $attribute = $this->getAttributeMock(
            [
                'id'            => 42,
                'virtualGroup'  => $group,
                'code'          => 'price',
                'label'         => 'Price',
                'sortOrder'     => 0,
                'scopable'      => false,
                'attributeType' => 'pim_catalog_price_collection'
            ]
        );

        $value = $this->getValueMock(
            [
                'attribute' => $attribute,
                'removable' => false,
            ]
        );

        $view = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($value, $view);

        $formView = [
            1 => [
                'label'      => 'General',
                'attributes' => [
                    'price_' => [
                        'isRemovable'        => false,
                        'allowValueCreation' => false,
                        'code'               => 'price',
                        'label'              => 'Price',
                        'id'                 => 42,
                        'locale'             => null,
                        'sortOrder'          => 0,
                        'classes'            => [
                            'currency' => true
                        ],
                        'value'              => $view,
                    ],
                ],
            ],
        ];

        $this->assertEquals($formView, $this->formView->getView());
    }

    /**
     * Test related method
     */
    public function testAttributeSortingInsideGroups()
    {
        $generalGroup = $this->getGroupMock(
            [
                'id'    => 1,
                'label' => 'General',
            ]
        );
        $otherGroup = $this->getGroupMock(
            [
                'id'    => 2,
                'label' => 'Other',
            ]
        );

        $nameAttr = $this->getAttributeMock(
            [
                'id'           => 42,
                'virtualGroup' => $generalGroup,
                'code'         => 'name',
                'label'        => 'Name',
                'sortOrder'    => 10,
                'scopable'     => false,
            ]
        );
        $nameValue = $this->getValueMock(
            [
                'attribute' => $nameAttr,
                'removable' => true,
            ]
        );

        $colorAttr = $this->getAttributeMock(
            [
                'id'           => 1337,
                'virtualGroup' => $generalGroup,
                'code'         => 'color',
                'label'        => 'Color',
                'sortOrder'    => 0,
                'scopable'     => false,
            ]
        );
        $colorValue = $this->getValueMock(
            [
                'attribute' => $colorAttr,
                'removable' => false,
            ]
        );

        $priceAttr = $this->getAttributeMock(
            [
                'id'           => 14,
                'virtualGroup' => $generalGroup,
                'code'         => 'price',
                'label'        => 'Price',
                'sortOrder'    => 20,
                'scopable'     => false,
            ]
        );
        $priceValue = $this->getValueMock(
            [
                'attribute' => $priceAttr,
                'removable' => true,
            ]
        );

        $releaseAttr = $this->getAttributeMock(
            [
                'id'           => 1987,
                'virtualGroup' => $otherGroup,
                'code'         => 'release_date',
                'label'        => 'Release date',
                'sortOrder'    => 20,
                'scopable'     => false,
            ]
        );
        $releaseValue = $this->getValueMock(
            [
                'attribute' => $releaseAttr,
                'removable' => true,
            ]
        );

        $weightAttr = $this->getAttributeMock(
            [
                'id'           => 73,
                'virtualGroup' => $otherGroup,
                'code'         => 'weight',
                'label'        => 'Weight',
                'sortOrder'    => 10,
                'scopable'     => false,
            ]
        );
        $weightValue = $this->getValueMock(
            [
                'attribute' => $weightAttr,
                'removable' => true,
            ]
        );

        $nameView    = $this->getMock('Symfony\Component\Form\FormView');
        $colorView   = $this->getMock('Symfony\Component\Form\FormView');
        $priceView   = $this->getMock('Symfony\Component\Form\FormView');
        $releaseView = $this->getMock('Symfony\Component\Form\FormView');
        $weightView  = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($nameValue, $nameView);
        $this->formView->addChildren($priceValue, $priceView);
        $this->formView->addChildren($colorValue, $colorView);
        $this->formView->addChildren($releaseValue, $releaseView);
        $this->formView->addChildren($weightValue, $weightView);

        $result = $this->formView->getView();
        $this->assertEquals(['color_', 'name_', 'price_'], array_keys($result[1]['attributes']));
        $this->assertEquals(['weight_', 'release_date_'], array_keys($result[2]['attributes']));
    }

    public function testAddLocalizableChildren()
    {
        $group = $this->getGroupMock(
            [
                'id'    => 1,
                'label' => 'General',
            ]
        );

        $attribute = $this->getAttributeMock(
            [
                'id'           => 42,
                'virtualGroup' => $group,
                'code'         => 'name',
                'label'        => 'Name',
                'sortOrder'    => 0,
                'scopable'     => false,
            ]
        );

        $valueFr = $this->getValueMock(
            [
                'attribute' => $attribute,
                'removable' => true,
                'locale'    => 'fr_FR'
            ]
        );

        $valueEn = $this->getValueMock(
            [
                'attribute' => $attribute,
                'removable' => true,
                'locale'    => 'en_US'
            ]
        );

        $viewFr = $this->getMock('Symfony\Component\Form\FormView');
        $viewEn = $this->getMock('Symfony\Component\Form\FormView');

        $this->formView->addChildren($valueFr, $viewFr);
        $this->formView->addChildren($valueEn, $viewEn);

        $formView = [
            1 => [
                'label'      => 'General',
                'attributes' => [
                    'name_fr_FR' => [
                        'isRemovable'        => true,
                        'allowValueCreation' => false,
                        'code'               => 'name',
                        'label'              => 'Name',
                        'sortOrder'          => 0,
                        'value'              => $viewFr,
                        'id'                 => 42,
                        'locale'             => 'fr_FR',
                    ],
                    'name_en_US' => [
                        'isRemovable'        => true,
                        'allowValueCreation' => false,
                        'code'               => 'name',
                        'label'              => 'Name',
                        'sortOrder'          => 0,
                        'value'              => $viewEn,
                        'id'                 => 42,
                        'locale'             => 'en_US',
                    ],
                ],
            ],
        ];

        $this->assertEquals($formView, $this->formView->getView());
    }

    /**
     * @param array $options
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductValue
     */
    private function getValueMock(array $options)
    {
        $options = array_merge(
            [
                'attribute' => null,
                'removable' => null,
                'scope'     => null,
                'locale'    => null,
            ],
            $options
        );

        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($options['attribute']));

        $value->expects($this->any())
            ->method('isRemovable')
            ->will($this->returnValue($options['removable']));

        $value->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue($options['scope']));

        $value->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($options['locale']));

        return $value;
    }

    /**
     * @param array $options
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Attribute
     */
    private function getAttributeMock(array $options)
    {
        $options = array_merge(
            [
                'id'            => null,
                'virtualGroup'  => null,
                'code'          => null,
                'label'         => null,
                'sortOrder'     => null,
                'scopable'      => null,
                'attributeType' => null,
            ],
            $options
        );

        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

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
            ->method('isScopable')
            ->will($this->returnValue($options['scopable']));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($options['attributeType']));

        return $attribute;
    }

    /**
     * @param array $options
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeGroup
     */
    private function getGroupMock(array $options)
    {
        $options = array_merge(
            [
                'id'    => null,
                'label' => null,
            ],
            $options
        );

        $group = $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeGroup');

        $group->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($options['id']));

        $group->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue($options['label']));

        return $group;
    }
}
