<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor;

/**
 * Extracts event names from a php files.
 *
 */
class EventTranslationExtractor extends PhpExtractor
{
    /**
     * Prefix for new found message.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * @var EventNamesExtractor
     */
    protected $extractor = null;

    public function __construct(EventNamesExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalog)
    {
        $messages = $this->extractor->extract($directory);

        foreach ($messages as $message) {
            $catalog->set($message, $this->prefix . $message);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}
