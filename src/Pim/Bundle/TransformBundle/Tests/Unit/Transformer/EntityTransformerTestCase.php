<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer;

use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;

/**
 * Test case for ORM transformers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class EntityTransformerTestCase extends \PHPUnit_Framework_TestCase
{
    protected $doctrine;
    protected $propertyAccessor;
    protected $guesser;
    protected $columnInfoTransformer;
    protected $transformers;
    protected $columnInfos;
    protected $repository;
    protected $manager;
    protected $metadata;

    protected function setUp()
    {
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));
        $this->metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->metadata));
        $this->propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $this->propertyAccessor
            ->expects($this->any())
            ->method('setValue')
            ->will(
                $this->returnCallback(
                    function ($object, $propertyPath, $value) {
                        $object->$propertyPath = $value;
                    }
                )
            );
        $this->guesser = $this->getMock('Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface');
        $this->guesser->expects($this->any())
            ->method('getTransformerInfo')
            ->will($this->returnCallback(array($this, 'getTransformer')));
        $this->columnInfoTransformer = $this
            ->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface');
        $this->columnInfoTransformer->expects($this->any())
            ->method('transform')
            ->will($this->returnCallback(array($this, 'getColumnInfo')));
        $this->transformers = array();
        $this->columnInfos = array();
    }

    protected function setupRepositories($referable = true)
    {
        if ($referable) {
            $this->repository = $this
                ->getMock('Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface');
            $this->repository->expects($this->any())
                ->method('getReferenceProperties')
                ->will($this->returnValue(array('code')));
        } else {
            $this->repository = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Doctrine\EntityRepository')
                ->disableOriginalConstructor()
                ->getMock();
        }

        $this->doctrine
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue($this->repository));
    }

    protected function addTransformer($propertyPath, $failing = false, $skipped = false, $withUpdater = false)
    {
        $this->transformers[$propertyPath] = $this
            ->getPropertyTransformerMock($propertyPath, $failing, $skipped, $withUpdater);
    }

    protected function getPropertyTransformerMock($prefix, $failing = false, $skipped = false, $withUpdater = false)
    {
        if ($skipped) {
            $transformer = $this->getMock('Pim\Bundle\TransformBundle\Transformer\Property\SkipTransformer');
        } elseif ($withUpdater) {
            $transformer = $this
                ->getMock('Pim\Bundle\TransformBundle\Tests\Stub\EntityUpdaterPropertyTransformerInterface');
        } else {
            $transformer = $this
                ->getMock('Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface');
        }
        if ($failing) {
            $transformer->expects($this->any())
                ->method('transform')
                ->will(
                    $this->throwException(
                        new PropertyTransformerException('error_message', array('error_parameters'))
                    )
                );
        } else {
            $transformer->expects($this->any())
                ->method('transform')
                ->will(
                    $this->returnCallback(
                        function ($value) use ($prefix) {
                            return "$prefix-$value";
                        }
                    )
                );
        }

        if ($withUpdater) {
            $transformer->expects($this->any())
                ->method('setValue')
                ->will(
                    $this->returnCallback(
                        function ($entity, $columnInfo, $data) {
                            $property = $columnInfo->getPropertyPath();
                            $entity->$property = $data . '_entityupdater';
                        }
                    )
                );
        }

        return $transformer;
    }

    public function getTransformer($columnInfo)
    {
        return isset($this->transformers[$columnInfo->getPropertyPath()])
            ? array($this->transformers[$columnInfo->getPropertyPath()], array())
            : null;
    }

    public function getColumnInfo($class, $label)
    {
        return is_array($label)
            ? array_intersect_key($this->columnInfos, array_flip($label))
            : $this->columnInfos[$label];
    }

    protected function addColumn(
        $label,
        $addTransformer = true,
        $skipped = false,
        $withUpdater = false,
        $suffixes = array()
    ) {
        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $columnInfo->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue($label));
        $columnInfo->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue($suffixes));
        $columnInfo->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue($label . '_path'));
        $this->columnInfos[$label] = $columnInfo;

        if ($addTransformer) {
            $this->addTransformer($label . '_path', false, $skipped, $withUpdater);
        }

        return $columnInfo;
    }
}
