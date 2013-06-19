<?php
namespace Pim\Bundle\ConfigBundle\Tests\Functional\Controller;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelControllerTest extends ControllerTest
{
    /**
     * @staticvar string
     */
    const CHANNEL_CODE = 'channelcode';

    /**
     * @staticvar string
     */
    const CHANNEL_NAME = 'Channel name';

    /**
     * @staticvar string
     */
    const CHANNEL_EDITED_NAME = 'Channel edited name';

    /**
     * @staticvar string
     */
    const CHANNEL_SAVED_MSG = 'Channel successfully saved';

    /**
     * @staticvar string
     */
    const CHANNEL_REMOVED_MSG ='Channel successfully removed';


    /**
     * Test related action
     */
    public function testIndex()
    {
        $uri = '/configuration/channel/';

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     *
     * @return null
     */
    public function testCreate()
    {
        $uri = '/configuration/channel/create';

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert channel form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/configuration\/channel\/create$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_config_channel_form[code]' => self::CHANNEL_CODE,
            'pim_config_channel_form[name]' => self::CHANNEL_NAME,
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::CHANNEL_SAVED_MSG);

        // assert entity well inserted
        $channel = $this->getRepository()->findOneBy(array('code' => self::CHANNEL_CODE));
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Channel', $channel);
        $this->assertEquals(self::CHANNEL_NAME, $channel->getName());
        $this->assertEquals(self::CHANNEL_CODE, $channel->getCode());
    }

    /**
     * Test related action
     *
     * @return null
     */
    public function testEdit()
    {
        // initialize authentication to call container and get channel entity
        $channel = $this->getRepository()->findOneBy(array('code' => self::CHANNEL_CODE));
        $uri = '/configuration/channel/edit/'. $channel->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert channel form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/configuration\/channel\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_config_channel_form[code]' => self::CHANNEL_CODE,
            'pim_config_channel_form[name]' => self::CHANNEL_EDITED_NAME,
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::CHANNEL_SAVED_MSG);

        // assert entity well inserted
        $channel = $this->getRepository()->findOneBy(array('code' => self::CHANNEL_CODE));
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Channel', $channel);
        $this->assertEquals(self::CHANNEL_EDITED_NAME, $channel->getName());
        $this->assertEquals(self::CHANNEL_CODE, $channel->getCode());

        // assert with unknown channel id and authentication
        $uri = '/configuration/channel/edit/0';
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     */
    public function testRemove()
    {
        // initialize authentication to call container and get channel entity
        $channel = $this->getRepository()->findOneBy(array('code' => self::CHANNEL_CODE));
        $uri = '/configuration/channel/remove/'. $channel->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFlashBagMessage($crawler, self::CHANNEL_REMOVED_MSG);

        // assert with unknown channel id (last removed) and authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimConfigBundle:Channel');
    }
}
