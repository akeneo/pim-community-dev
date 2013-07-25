<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\DependencyInjection\Reference;

use Pim\Bundle\ImportExportBundle\DependencyInjection\Reference\ReferenceFactory;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateReference()
    {
        $factory = new ReferenceFactory;
        $reference = $factory->createReference('foo.bar');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $reference);
        $this->assertEquals('foo.bar', (string) $reference);
    }
}
