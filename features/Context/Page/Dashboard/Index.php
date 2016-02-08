<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Context\Page\Dashboard;

use Context\Page\Base\Base;

/**
 * Dashboard page
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 */
class Index extends Base
{
    /** @var string */
    protected $path = '/';

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
                    'decorators' => ['PimEnterprise\Behat\Decorator\WidgetDecorator\ProposalWidgetDecorator']
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
