<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;

/**
 * Test case for ORM transformers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ORMTransformerTestCase extends \PHPUnit_Framework_TestCase
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
        $this->guesser = $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface');
        $this->guesser->expects($this->any())
            ->method('getTransformerInfo')
            ->will($this->returnCallback([$this, 'getTransformer']));
        $this->columnInfoTransformer = $this
            ->getMock('Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface');
        $this->columnInfoTransformer->expects($this->any())
            ->method('transform')
            ->will($this->returnCallback([$this, 'getColumnInfo']));
        $this->transformers = [];
        $this->columnInfos = [];
        $this->setupRepositories();
    }

    protected function setupRepositories()
    {
        $this->repository = $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface');
        $this->repository->expects($this->any())
            ->method('getReferenceProperties')
            ->will($this->returnValue(['code']));

        $this->doctrine
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue($this->repository));
    }

    protected function addTransformer($propertyPath, $failing = false)
    {
        $this->transformers[$propertyPath] = $this->getPropertyTransformerMock($propertyPath, $failing);
    }

    protected function getPropertyTransformerMock($prefix, $failing = false)
    {
        $transformer = $this->getMock(
            'Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface'
        );
        if ($failing) {
            $transformer->expects($this->any())
                ->method('transform')
                ->will(
                    $this->throwException(
                        new PropertyTransformerException('error_message', ['error_parameters'])
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

        return $transformer;
    }

    public function getTransformer($columnInfo)
    {
        return isset($this->transformers[$columnInfo->getPropertyPath()])
            ? [$this->transformers[$columnInfo->getPropertyPath()], []]
            : null;
    }

    public function getColumnInfo($class, $label)
    {
        return is_array($label)
            ? array_intersect_key($this->columnInfos, array_flip($label))
            : $this->columnInfos[$label];
    }

    protected function addColumn($label, $addTransformer = true)
    {
        $columnInfo = $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $columnInfo->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue($label));
        $columnInfo->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue([]));
        $columnInfo->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue($label . '_path'));
        $this->columnInfos[$label] = $columnInfo;

        if ($addTransformer) {
            $this->addTransformer($label . '_path');
        }

        return $columnInfo;
    }
}
