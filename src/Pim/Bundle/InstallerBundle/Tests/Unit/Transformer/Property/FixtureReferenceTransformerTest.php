<?php

namespace Pim\Bundle\InstallerBundle\Tests\Unit\Property;

use Pim\Bundle\InstallerBundle\Transformer\Property\FixtureReferenceTransformer;

/**
 * Description of FixtureReferenceTransformerTest
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixtureReferenceTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $transformer;
    protected $entityTransformer;
    protected $referenceRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->entityTransformer = $this->getMock(
            'Pim\Bundle\ImportExportBundle\Transformer\Property\AssociationTransformerInterface'
        );
        $this->transformer = new FixtureReferenceTransformer($this->entityTransformer);
        $this->referenceRepository = $this->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer->setReferenceRepository($this->referenceRepository);
    }

    /**
     * Test related method
     */
    public function testGetFixtureEntity()
    {
        $this->referenceRepository->expects($this->once())
            ->method('hasReference')
            ->with($this->equalTo('class.code'))
            ->will($this->returnValue(true));
        $this->referenceRepository->expects($this->once())
            ->method('getReference')
            ->with($this->equalTo('class.code'))
            ->will($this->returnValue('success'));
        $this->entityTransformer->expects($this->never())
            ->method('getEntity');

        $this->assertEquals('success', $this->transformer->getEntity('class', 'code'));
    }

    /**
     * Test related method
     */
    public function testGetTransformerEntity()
    {
        $this->referenceRepository->expects($this->once())
            ->method('hasReference')
            ->with($this->equalTo('class.code'))
            ->will($this->returnValue(false));
        $this->referenceRepository->expects($this->never())
            ->method('getReference');
        $this->entityTransformer->expects($this->once())
            ->method('getEntity')
            ->with($this->equalTo('class'), $this->equalTo('code'))
            ->will($this->returnValue('success'));

        $this->assertEquals('success', $this->transformer->getEntity('class', 'code'));
    }
}
