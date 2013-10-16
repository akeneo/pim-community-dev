<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Provider;

use Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider;

class LocaleSettingsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocaleSettingsProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new LocaleSettingsProvider();
    }

    protected function tearDown()
    {
        unset($this->provider);
    }

    public function testAddNameFormats()
    {
        $enFormat = '%first_name% %middle_name% %last_name%';
        $enFormatModified = '%prefix% %%first_name% %middle_name% %last_name% %suffix%';
        $ruFormat = '%last_name% %first_name% %middle_name%';

        $this->assertAttributeEmpty('nameFormats', $this->provider);

        $this->provider->addNameFormats(array('en' => $enFormat));
        $this->assertAttributeEquals(
            array('en' => $enFormat),
            'nameFormats',
            $this->provider
        );

        $this->provider->addNameFormats(array('en' => $enFormatModified, 'ru' => $ruFormat));
        $this->assertAttributeEquals(
            array('en' => $enFormatModified, 'ru' => $ruFormat),
            'nameFormats',
            $this->provider
        );
    }

    public function testAddAddressFormats()
    {
        $usFormat = '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%';
        $usFormatModified = '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY% %postal_code%';
        $ruFormat = '%postal_code% %COUNTRY% %CITY%\n%STREET%\n%organization%\n%name%';

        $this->assertAttributeEmpty('addressFormats', $this->provider);

        $this->provider->addAddressFormats(array('US' => $usFormat));
        $this->assertAttributeEquals(
            array('US' => $usFormat),
            'addressFormats',
            $this->provider
        );

        $this->provider->addAddressFormats(array('US' => $usFormatModified, 'RU' => $ruFormat));
        $this->assertAttributeEquals(
            array('US' => $usFormatModified, 'RU' => $ruFormat),
            'addressFormats',
            $this->provider
        );
    }
}
