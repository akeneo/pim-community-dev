<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\AttributeOptionGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionGuesserTest extends GuesserTestCase
{
    protected $doctrine;
    protected $manager;
    protected $relatedMetadata;
    protected $propertyPath = 'options';

    protected function setUp()
    {
        parent::setUp();
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $this->columnInfo->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('name'));
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
            ->with($this->equalTo('options'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->any())
            ->method('getAssociationMapping')
            ->with($this->equalTo('options'))
            ->will(
                $this->returnValue(array('targetEntity' => 'target_entity', 'type' => $type))
            );

        $repository = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface');
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('target_entity'))
            ->will($this->returnValue($repository));

        $guesser = new AttributeOptionGuesser($this->transformer, $this->doctrine, 'class');
        $this->assertEquals(
            array(
                $this->transformer,
                array('class' => 'target_entity', 'multiple' => $multiple, 'reference_prefix' => 'name')
            ),
            $guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNoAssociation()
    {
        $this->metadata
            ->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('options'))
            ->will($this->returnValue(false));
        $guesser = new AttributeOptionGuesser($this->transformer, $this->doctrine, 'class');
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testBadClass()
    {
        $guesser = new AttributeOptionGuesser($this->transformer, $this->doctrine, 'other_class');
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testNotReferable()
    {
        $this->metadata
            ->expects($this->once())
            ->method('hasAssociation')
            ->with($this->equalTo('options'))
            ->will($this->returnValue(true));

        $this->metadata->expects($this->any())
            ->method('getAssociationMapping')
            ->with($this->equalTo('options'))
            ->will(
                $this->returnValue(array('targetEntity' => 'target_entity', 'type' => ClassMetadataInfo::MANY_TO_MANY))
            );

        $repository = new \stdClass;
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('target_entity'))
            ->will($this->returnValue($repository));

        $guesser = new AttributeOptionGuesser($this->transformer, $this->doctrine, 'class');
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
