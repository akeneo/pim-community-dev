<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;

class EntityFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var EntityFilterType
     */
    private $type;

    protected function setUp()
    {
        $translator = $this->createMockTranslator();

        $registry = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ManagerRegistry', array(), '', false);

        $types = array(
            new FilterType($translator),
            new ChoiceFilterType($translator),
            new EntityType($registry)
        );

        $this->formExtensions[] = new CustomFormExtension($types);

        parent::setUp();

        $this->type = new EntityFilterType($translator);
    }

    /**
     * @return EntityFilterType
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    public function testGetName()
    {
        $this->assertEquals(EntityFilterType::NAME, $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals(ChoiceFilterType::NAME, $this->type->getParent());
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'entity',
                    'field_options' => array(),
                    'translatable'  => false,
                )
            )
        );
    }

    /**
     * @dataProvider bindDataProvider
     * @param array $bindData
     * @param array $formData
     * @param array $viewData
     * @param array $customOptions
     */
    public function testBindData(
        array $bindData,
        array $formData,
        array $viewData,
        array $customOptions = array()
    ) {
        // bind method should be tested in functional test
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        return array(
            'empty' => array(
                'bindData' => array(),
                'formData' => array(),
                'viewData' => array(),
            ),
        );
    }
}
