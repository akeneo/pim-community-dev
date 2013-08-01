<?php

namespace Context\Page\Channel;

use Context\Page\Base\Grid;

/**
 * Channel index page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    protected $path = '/configuration/channel/';

    public function findChannelRow($name)
    {
        return $this->getElement('Grid content')->find('css', sprintf('tr:contains("%s")', $name));
    }

    public function channelCanExport($channel, $category)
    {
        return $this->getElement('Grid content')->find(
            'css', sprintf('tr:contains("%s"):contains("%s")', $channel, $category)
        );
    }
}
