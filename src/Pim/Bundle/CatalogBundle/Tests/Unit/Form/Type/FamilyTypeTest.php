<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type;

use Pim\Bundle\CatalogBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;
use Pim\Bundle\CatalogBundle\Form\Type\FamilyType;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTypeTest extends AbstractFormTypeTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // Create form type
        $this->type = new FamilyType($this->getRequirementsSubscriber());
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('code', 'text');

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\CatalogBundle\Entity\Family',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_catalog_family', $this->form->getName());
    }

    /**
     * Get attribute requirements subscriber mock
     *
     * @return AddAttributeRequirementsSubscriber
     */
    private function getRequirementsSubscriber()
    {
        $channelManager = $this->getChannelManagerMock();

        return new AddAttributeRequirementsSubscriber($channelManager);
    }

    /**
     * @return ChannelManager
     */
    private function getChannelManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
