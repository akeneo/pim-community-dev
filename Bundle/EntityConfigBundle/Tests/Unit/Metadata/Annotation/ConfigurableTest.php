<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata\Annotation;

use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfig;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Configurable;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTrue
     */
    public function testTrue(array $data)
    {
        $annot = new Configurable($data);
        $this->assertEquals(AbstractConfig::MODE_VIEW_HIDDEN, $annot->viewMode);
        $this->assertEquals('symfony_route_name', $annot->routeName);
        $this->assertEquals(array('key' => 'value'), $annot->defaultValues);
    }

    /**
     * @dataProvider providerFalse
     */
    public function testFalse(array $data, $exceptionClass, $exceptionMessage)
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);

        $annot = new Configurable($data);
    }

    public function providerTrue()
    {
        return array(
            array(
                array(
                    'value'         => AbstractConfig::MODE_VIEW_HIDDEN,
                    'routeName'     => 'symfony_route_name',
                    'defaultValues' => array('key' => 'value'),
                ),
            ),
            array(
                array(
                    'viewMode'      => AbstractConfig::MODE_VIEW_HIDDEN,
                    'routeName'     => 'symfony_route_name',
                    'defaultValues' => array('key' => 'value'),
                ),
            )
        );
    }

    public function providerFalse()
    {
        return array(
            array(
                array(
                    'viewMode'      => 'wrong_value',
                    'routeName'     => 'symfony_route_name',
                    'defaultValues' => array('key' => 'value'),
                ),
                'Oro\Bundle\EntityConfigBundle\Exception\AnnotationException',
                'Annotation "Configurable" give invalid parameter "viewMode" : "wrong_value"'
            ),
            array(
                array(
                    'viewMode'      => AbstractConfig::MODE_VIEW_HIDDEN,
                    'routeName'     => 'symfony_route_name',
                    'defaultValues' => 'wrong_value',
                ),
                'Oro\Bundle\EntityConfigBundle\Exception\AnnotationException',
                'Annotation "Configurable" parameter "defaultValues" expect "array" but "string" given'
            )
        );
    }
}
