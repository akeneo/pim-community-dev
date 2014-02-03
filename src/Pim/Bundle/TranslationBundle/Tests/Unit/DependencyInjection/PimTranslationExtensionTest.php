<?php

namespace Pim\Bundle\TranslationBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Pim\Bundle\TranslationBundle\DependencyInjection\PimTranslationExtension;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimTranslationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Extension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $configs = array();

    /**
     * @var ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * Form type translation service id
     * @staticvar string
     */
    const PIM_TRANSLATION_FORM_TYPE = 'pim_translation.form.type.translatable_field';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->extension = new PimTranslationExtension();
        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * Test related method
     */
    public function testLoad()
    {
        $this->extension->load($this->configs, $this->containerBuilder);

        $serviceIds = $this->containerBuilder->getServiceIds();
        $this->assertCount(3, $serviceIds);
        $this->assertTrue(in_array(self::PIM_TRANSLATION_FORM_TYPE, $serviceIds));
    }
}
