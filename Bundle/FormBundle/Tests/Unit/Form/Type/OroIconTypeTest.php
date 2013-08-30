<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroIconType;
use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;

class OroIconTypeTest extends FormIntegrationTestCase
{
    /**
     * @var OroIconType
     */
    private $type;

    protected function setUp()
    {
        $this->type = new OroIconType();
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    public function testParameters()
    {
        $this->assertEquals('genemu_jqueryselect2_hidden', $this->type->getParent());
        $this->assertEquals('oro_icon_select', $this->type->getName());
    }
}
