<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\TransformBundle\Transformer\Guesser\RelationGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RelationGuesserTest extends GuesserTestCase
{
    protected $doctrine;
    protected $manager;

    protected function setUp()
    {
        parent::setUp();
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));
    }

    public function getMatchingData()
    {
        return [
            'multiple'  => [ClassMetadataInfo::MANY_TO_MANY, true],
            'single'    => [ClassMetadataInfo::MANY_TO_ONE, false],
        ];
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
                $this->returnValue(['targetEntity' => 'target_entity', 'type' => $type])
            );

        $repository = $this->getMock(
            'Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface'
        );
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('target_entity'))
            ->will($this->returnValue($repository));

        $guesser = new RelationGuesser($this->transformer, $this->doctrine);
        $this->assertEquals(
            [$this->transformer, ['class' => 'target_entity', 'multiple' => $multiple]],
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
        $guesser = new RelationGuesser($this->transformer, $this->doctrine);
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testNotReferable()
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
                $this->returnValue(['targetEntity' => 'target_entity', 'type' => ClassMetadataInfo::MANY_TO_MANY])
            );

        $repository = new \stdClass();
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('target_entity'))
            ->will($this->returnValue($repository));

        $guesser = new RelationGuesser($this->transformer, $this->doctrine);
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
