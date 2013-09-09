<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\Views;

class AbstractViewsListTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $list;

    public function setUp()
    {
        $this->translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $this->list       = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\Views\AbstractViewsList',
            array($this->translator)
        );
    }

    public function tearDown()
    {
        unset($this->translator);
        unset($this->list);
    }

    /**
     * @dataProvider viewsDataProvider
     *
     * @param array $viewsArray
     * @param array $shouldContain
     * @param array $expectedException
     */
    public function testGetList($viewsArray, $shouldContain, $expectedException = array())
    {

    }
}
