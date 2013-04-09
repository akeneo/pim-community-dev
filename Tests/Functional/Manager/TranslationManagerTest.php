<?php
namespace Pim\Bundle\TranslationBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Pim\Bundle\TranslationBundle\Manager\TranslationManager;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TranslationManagerTest extends WebTestCase
{

    /**
     * Test instanciation with service
     */
    public function testServiceCall()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $manager = static::$kernel->getContainer()->get('pim_translation.translation_manager');

        $this->assertInstanceOf('Pim\Bundle\TranslationBundle\Manager\TranslationManager', $manager);
    }
}
