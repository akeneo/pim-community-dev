<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Pim\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleEntityFilter;
use Oro\Bundle\GridBundle\Filter\ORM\EntityFilter;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleEntityFilterTest extends FlexibleFilterTestCase
{
    const TEST_CLASS = 'test_class';
    const TEST_ATT_ID = 1;
    const TEST_ATT_BACKENDSTORAGE = 'test_storage';
    const TEST_ATT_BACKENDTYPE = 'test_type';
    const TEST_ROOT_ALIAS = 'root_alias';
    const TEST_VALUE = 'test_value';
    const TEST_JOIN_CONDITION = 'test_condition';

    /**
     * {@inheritdoc}
     */
    protected function createTestFilter($flexibleRegistry)
    {
        $parentFilter = new EntityFilter($this->getTranslatorMock());

        $flexibleEntityFilter = $this->getMock(
            'Pim\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleEntityFilter',
            array('getClassName'),
            array($flexibleRegistry, $parentFilter)
        );

        $flexibleEntityFilter->expects($this->any())
                             ->method('getClassName')
                             ->will($this->returnValue(self::TEST_CLASS));

        return $flexibleEntityFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function filterDataProvider()
    {
        return array(
            'correct_equals' => array(
                'data' => array('value' => $this->createEntities(2), 'type' => ChoiceFilterType::TYPE_CONTAINS),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, array(0 => 0, 1 => 1), 'IN'), null)
                )
            ),
            'with_type_null' => array(
                'data' => array('value' => $this->createEntities(2), 'type' => null),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, array(0 => 0, 1 => 1), 'IN'), null)
                )
            ),
            'with_type_not_contains' => array(
                'data' => array('value' => $this->createEntities(2), 'type' => ChoiceFilterType::TYPE_NOT_CONTAINS),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, array(0 => 0, 1 => 1), 'NOT IN'), null)
                )
            )
        );
    }

    /**
     * Data provider with incorrect datas
     *
     * @return array
     */
    public function filterWithIncorrectDataProvider()
    {
        return array(
            'without_data' => array(
                'data' => array()
            ),
            'with_null_value' => array(
                'data' => array('value' => null, 'type' => ChoiceFilterType::TYPE_CONTAINS)
            ),
            'with_empty_array_value' => array(
                'data' => array('value' => array(), 'type' => ChoiceFilterType::TYPE_CONTAINS)
            ),
            'with_empty_collection_value' => array(
                'data' => array('value' => new ArrayCollection(), 'type' => ChoiceFilterType::TYPE_CONTAINS)
            )
        );
    }

    /**
     * @param array $data
     * @param array $expectRepositoryCalls
     *
     * @dataProvider filterWithIncorrectDataProvider
     */
    public function testFilterWithIncorrectData(array $data = array(), array $expectRepositoryCalls = array())
    {
        parent::testFilter($data, $expectRepositoryCalls);

        $this->assertFalse($this->model->isActive());
    }

    /**
     * Create entities for choice
     *
     * @param int $count the number of entities to create
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function createEntities($count)
    {
        $entities = new ArrayCollection();

        for ($i=0; $i < $count; $i++) {
            $object = $this->getMock('\stdClass', array('getId'));
            $object->expects($this->any())
                   ->method('getId')
                   ->will($this->returnValue($i));

            $entities->add($object);
        }

        return $entities;
    }

    /**
     * {@inheritdoc}
     * @dataProvider filterDataProvider
     */
    public function testFilter(array $data = array(), array $expectRepositoryCalls = array())
    {
        parent::testFilter($data, $expectRepositoryCalls);

        $this->assertTrue($this->model->isActive());
    }

    /**
     * {@inheritdoc}
     */
    protected function createFlexibleManager(FlexibleEntityRepository $entityRepository)
    {
        $flexibleManager = parent::createFlexibleManager($entityRepository);

        $attributeRepository = $this->createAttributeRepository();

        $flexibleManager->expects($this->any())
                        ->method('getAttributeRepository')
                        ->will($this->returnValue($attributeRepository));

        return $flexibleManager;
    }

    /**
     * Create attribute repository
     *
     * @return Doctrine\Common\Persistence\ObjectRepository
     */
    protected function createAttributeRepository()
    {
        $this->attribute = $this->createAttribute();

        $attributeRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $attributeRepository->expects($this->any())
                            ->method('findOneBy')
                            ->with(array('code' => self::TEST_FIELD))
                            ->will($this->returnValue($this->attribute));

        return $attributeRepository;
    }

    /**
     * Create attribute mock
     *
     * @return Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected function createAttribute()
    {
        $attribute = $this->getMockforAbstractClass('Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute');

        $attribute->expects($this->any())
                  ->method('getId')
                  ->will($this->returnValue(self::TEST_ATT_ID));

        $attribute->expects($this->any())
                  ->method('getBackendStorage')
                  ->will($this->returnValue(self::TEST_ATT_BACKENDSTORAGE));

        $attribute->expects($this->any())
                  ->method('getBackendType')
                  ->will($this->returnValue(self::TEST_ATT_BACKENDTYPE));

        return $attribute;
    }
}
