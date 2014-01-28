<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\DependencyInjection\Reference;

use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testCreateReference()
    {
        $factory = new ReferenceFactory();
        $reference = $factory->createReference('foo.bar');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $reference);
        $this->assertEquals('foo.bar', (string) $reference);
    }
}
