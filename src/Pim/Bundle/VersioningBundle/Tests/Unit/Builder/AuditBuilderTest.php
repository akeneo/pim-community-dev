<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\Builder;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\VersioningBundle\Builder\AuditBuilder;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\Builder\AuditBuilder
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->manager = new AuditBuilder();
    }

    /**
     * Test related method
     */
    public function testBuildAudit()
    {
        $resourceName = 'myfakeresourcename';
        $resourceId = 1;
        $user = $this->getUserMock();
        $numVersion = 1;

        // update version
        $data = array('field1' => 'the-same', 'field2' => 'will-be-changed', 'field4' => 'old-data');
        $previousVersion = new Version($resourceName, $resourceId, $numVersion, $data, $user);

        $data = array('field1' => 'the-same', 'field2' => 'has-changed', 'field3' => 'new-data');
        $currentVersion = new Version($resourceName, $resourceId, $numVersion, $data, $user);

        $audit = $this->manager->buildAudit($currentVersion, $previousVersion);
        $expected = array(
            'field2' => array('old' => 'will-be-changed', 'new' => 'has-changed'),
            'field3' => array('old' => '', 'new' => 'new-data'),
            'field4' => array('old' => 'old-data', 'new' => ''),
        );
        $this->assertEquals($expected, $audit->getData());

        // new version
        $audit = $this->manager->buildAudit($currentVersion);
        $expected = array(
            'field1' => array('old' => '', 'new' => 'the-same'),
            'field2' => array('old' => '', 'new' => 'has-changed'),
            'field3' => array('old' => '', 'new' => 'new-data'),
        );
        $this->assertEquals($audit->getData(), $expected);
    }

    /**
     * @return User
     */
    protected function getUserMock()
    {
        return $this->getMock('Oro\Bundle\UserBundle\Entity\User');
    }
}
