<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\EntityGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityGuesserTest extends GuesserTestCase
{
    protected $doctrine;
    protected $manager;
    protected $relatedMetadata;

    protected function setUp()
    {
        parent::setUp();
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));
        $this->relatedMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo('target_entity'))
            ->will($this->returnValue($this->relatedMetadata));
    }

    public function getMatchingData()
    {
        return array(
            'multiple'  => array(ClassMetadataInfo::MANY_TO_MANY, true),
            'single'    => array(ClassMetadataInfo::MANY_TO_ONE, false),
        );
    }

    /**
     * @dataProvider getMatchingData
     */
    public function testMatching($type, $multiple)
    {
        $this->metadata
            ->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->any())
            ->method('getAssociationMapping')
            ->with($this->equalTo('property_path'))
            ->will(
                $this->returnValue(array('targetEntity' => 'target_entity', 'type' => $type))
            );

        $this->relatedMetadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('code'))
            ->will($this->returnValue(true));

        $guesser = new EntityGuesser($this->transformer, $this->doctrine);
        $this->assertEquals(
            array($this->transformer, array('class' => 'target_entity', 'multiple' => $multiple)),
            $guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNoAssociation()
    {
        $this->metadata
            ->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(false));
        $guesser = new EntityGuesser($this->transformer, $this->doctrine);
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testNoCodeField()
    {
        $this->metadata
            ->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->any())
            ->method('getAssociationMapping')
            ->with($this->equalTo('property_path'))
            ->will(
                $this->returnValue(array('targetEntity' => 'target_entity', 'type' => ClassMetadataInfo::MANY_TO_MANY))
            );

        $this->relatedMetadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('code'))
            ->will($this->returnValue(false));

        $guesser = new EntityGuesser($this->transformer, $this->doctrine);
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
