<?php
namespace Oro\Bundle\MeasureBundle\Tests\Unit\DependencyInjection;

use Pim\Bundle\ConfigBundle\DependencyInjection\LanguageConfiguration;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguageConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testGetConfigTreeBuilder()
    {
        $config = new LanguageConfiguration();
        $this->assertInstanceOf(
            '\Symfony\Component\Config\Definition\Builder\TreeBuilder',
            $config->getConfigTreeBuilder()
        );
    }
}
