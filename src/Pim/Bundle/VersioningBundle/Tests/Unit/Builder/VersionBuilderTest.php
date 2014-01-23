<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Builder;

use Symfony\Component\Serializer\Serializer;
use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ChainedUpdateGuesser;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\Builder\VersionBuilder
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $encoders = [new CsvEncoder()];
        $normalizers = [new GetSetMethodNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $this->manager = new VersionBuilder($serializer, new ChainedUpdateGuesser());
    }

    /**
     * Test related method
     */
    public function testBuildVersion()
    {
        $data = ['field' => 'value'];
        $version = $this->manager->buildVersion($this->getVersionableMock($data), $this->getUserMock(), 1);
        $this->assertTrue($version instanceof Version);
    }

    /**
     * @param array $data
     *
     * @return Product
     */
    protected function getVersionableMock(array $data)
    {
        $versionable = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $versionable->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $versionable->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(2));

        return $versionable;
    }

    /**
     * @return User
     */
    protected function getUserMock()
    {
        return $this->getMock('Oro\Bundle\UserBundle\Entity\User');
    }
}
