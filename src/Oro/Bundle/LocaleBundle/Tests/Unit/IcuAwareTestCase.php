<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit;

class IcuAwareTestCase extends \PHPUnit_Framework_TestCase
{
    protected function ignoreIfIcuVersionGreaterThan($version)
    {
        $actualVersion = $this->getIcuVersion();
        if (version_compare($actualVersion, $version, '>')) {
            $this->markTestSkipped(
                'This test is designed for ICU version <= ' . $version . ' but actual version is ' . $actualVersion
            );
        }
    }

    protected function getIcuVersion()
    {
        if (defined('INTL_ICU_VERSION')) {
            $version = INTL_ICU_VERSION;
        } else {
            $reflector = new \ReflectionExtension('intl');

            ob_start();
            $reflector->info();
            $output = strip_tags(ob_get_clean());

            preg_match('/^ICU version +(?:=> )?(.*)$/m', $output, $matches);
            $version = $matches[1];
        }
        return $version;
    }
}
