<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Model;

use Gedmo\Sluggable\Util\Urlizer;

/**
 * Value object which represent a project identifier
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectIdentifier
{
    /** @var string */
    protected $label;

    /** @var string */
    protected $channel;

    /** @var string */
    protected $locale;

    /**
     * @param string $label
     * @param string $channel
     * @param string $locale
     */
    public function __construct($label, $channel, $locale)
    {
        $this->label = $label;
        $this->channel = $channel;
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return Urlizer::transliterate(
            sprintf(
                '%s %s %s',
                $this->label,
                $this->channel,
                $this->locale
            )
        );
    }
}
