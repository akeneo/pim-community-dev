<?php
namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Channel index page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelIndex extends Page
{
    protected $path = '/configuration/channel/';

    protected $elements = array(
        'Channels' => array('css' => 'table.grid'),
    );

    public function findChannelRow($name)
    {
        return $this->getElement('Channels')->find('css', sprintf('tr:contains("%s")', $name));
    }

    public function channelCanExport($channel, $category)
    {
        return $this->getElement('Channels')->find(
            'css', sprintf('tr:contains("%s"):contains("%s")', $channel, $category)
        );
    }
}
