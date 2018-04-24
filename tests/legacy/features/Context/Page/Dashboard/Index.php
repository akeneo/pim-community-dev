<?php

namespace Context\Page\Dashboard;

use Context\Page\Base\Base;

/**
 * Dashboard page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Base
{
    /**
     * @var string
     */
    protected $path = '#/';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Proposal widget' => [
                    'css'        => '#proposal-widget',
                    'decorators' => ['PimEnterprise\Behat\Decorator\Widget\ProposalWidgetDecorator']
                ],
            ]
        );
    }

    /**
     * Get the channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     *
     * @return string
     */
    public function getChannelCompleteness($channel)
    {
        return $this->getElement('Completeness Widget')->getChannelCompleteness($channel);
    }

    /**
     * Get the localized channel completeness ratio inside the completeness widget
     *
     * @param string $channel
     * @param string $locale
     *
     * @return string
     */
    public function getLocalizedChannelCompleteness($channel, $locale)
    {
        return $this->getElement('Completeness Widget')->getLocalizedChannelCompleteness($channel, $locale);
    }
}
