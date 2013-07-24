<?php

namespace Oro\Bundle\ImapBundle\Connector;

use Oro\Bundle\ImapBundle\Connector\Search\SearchStringManagerInterface;
use Oro\Bundle\ImapBundle\Mail\Storage\Imap;

/**
 * Provides an access to IMAP services such as the storage and the query string builder
 */
class ImapServices
{
    /** @var Imap */
    private $imapStorage;

    /** @var SearchStringManagerInterface */
    private $searchStringManager;

    public function __construct(Imap $imapStorage, SearchStringManagerInterface $searchStringManager)
    {
        $this->imapStorage = $imapStorage;
        $this->searchStringManager = $searchStringManager;
    }

    /**
     * Gets the search string manager
     *
     * @return SearchStringManagerInterface
     */
    public function getSearchStringManager()
    {
        return $this->searchStringManager;
    }

    /**
     * Gets the IMAP storage
     *
     * @return Imap
     */
    public function getStorage()
    {
        return $this->imapStorage;
    }
}
