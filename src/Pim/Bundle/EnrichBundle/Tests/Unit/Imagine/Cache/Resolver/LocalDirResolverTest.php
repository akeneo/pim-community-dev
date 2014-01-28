<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\Imagine\Cache\Resolver;

use Symfony\Component\Filesystem\Filesystem;
use Pim\Bundle\EnrichBundle\Imagine\Cache\Resolver\LocalDirResolver;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalDirResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $cacheDir = '/tmp/phpunit/local-dir-resolver';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        @mkdir($this->cacheDir, 0777, true);

        $this->resolver = new LocalDirResolver(new Filesystem(), $this->cacheDir);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $it = new \RecursiveDirectoryIterator($this->cacheDir);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->cacheDir);
    }

    /**
     * Test related method
     */
    public function testResolveInexistentCachedImage()
    {
        $this->assertEquals(
            $this->cacheDir . '/foo_filter/image.gif',
            $this->resolver->resolve($this->getRequestMock(), 'image.gif', 'foo_filter')
        );
    }

    /**
     * Test related method
     */
    public function testResolveExistentCachedImage()
    {
        @mkdir($this->cacheDir . '/foo_filter', 0777, true);
        file_put_contents($this->cacheDir . '/foo_filter/image.gif', 'image content');

        $response = $this->resolver->resolve($this->getRequestMock(), 'image.gif', 'foo_filter');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertAttributeEquals('image content', 'content', $response);
    }

    /**
     * @expectedException \Exception
     * @expectedMessage
     *     The Pim\Bundle\CatalogBundle\Imagine\Cache\Resolver\LocalDirResolver is not meant to generate browser path
     */
    public function testGetBrowserPath()
    {
        $this->resolver->getBrowserPath('foo', 'bar');
    }

    /**
     * Test related method
     */
    public function testClear()
    {
        @mkdir($this->cacheDir . '/bar_filter', 0777, true);
        $images = array('image1.gif', 'image2.gif', 'image3.gif');
        foreach ($images as $image) {
            file_put_contents($this->cacheDir . '/bar_filter/' . $image, 'image content');
        }

        $this->resolver->clear('bar_filter');

        foreach ($images as $image) {
            if (is_file($this->cacheDir . '/bar_filter/' . $image)) {
                $this->fail(
                    sprintf('File "%s" was not cleared', $this->cacheDir . '/bar_filter/' . $image)
                );
            }
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequestMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }
}
