<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EntityFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var EntityFilterType
     */
    private $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();

        $registry = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ManagerRegistry', [], '', false);

        $types = [
            new FilterType($translator),
            new ChoiceFilterType($translator),
            new EntityType($registry)
        ];

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
        return [
            [
                'defaultOptions' => [
                    'field_type'    => 'entity',
                    'field_options' => [],
                    'translatable'  => false,
                ]
            ]
        ];
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
        array $customOptions = []
    ) {
        // bind method should be tested in functional test
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        return [
            'empty' => [
                'bindData' => [],
                'formData' => [],
                'viewData' => [],
            ],
        ];
    }
}
