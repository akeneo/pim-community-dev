<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata\Annotation;

use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTrue
     */
    public function testTrue(array $data)
    {
        $annot = new Config($data);
        $this->assertEquals(AbstractConfigModel::MODE_VIEW_HIDDEN, $annot->mode);
        $this->assertEquals('symfony_route_name', $annot->routeName);
        $this->assertEquals(array('key' => 'value'), $annot->defaultValues);
    }

    /**
     * @dataProvider providerFalse
     */
    public function testFalse(array $data, $exceptionClass, $exceptionMessage)
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);

        $annot = new Config($data);
    }

    public function providerTrue()
    {
        return array(
            array(
                array(
                    'value'         => AbstractConfigModel::MODE_VIEW_HIDDEN,
                    'routeName'     => 'symfony_route_name',
                    'defaultValues' => array('key' => 'value'),
                ),
            ),
            array(
                array(
                    'viewMode'      => AbstractConfigModel::MODE_VIEW_HIDDEN,
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
                'Annotation "Config" give invalid parameter "viewMode" : "wrong_value"'
            ),
            array(
                array(
                    'viewMode'      => AbstractConfigModel::MODE_VIEW_HIDDEN,
                    'routeName'     => 'symfony_route_name',
                    'defaultValues' => 'wrong_value',
                ),
                'Oro\Bundle\EntityConfigBundle\Exception\AnnotationException',
                'Annotation "Config" parameter "defaultValues" expect "array" but "string" given'
            )
        );
    }
}
