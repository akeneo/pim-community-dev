<?php

namespace Oro\Bundle\ImapBundle\Connector;

use Oro\Bundle\ImapBundle\Connector\Search\SearchQuery;
use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryBuilder;
use Oro\Bundle\ImapBundle\Connector\Search\SearchStringManagerInterface;
use Oro\Bundle\ImapBundle\Extensions\Zend\Mail\Storage\Imap;
use Zend\Mail\Storage\Folder;
use Zend\Mail\Storage\Message;

/**
 * A base class for connectors intended to work with email's servers through IMAP protocol.
 */
class ImapConnector
{
    /**
     * @var ImapConfig
     */
    protected $config;

    /**
     * @var ImapServicesFactory
     */
    protected $factory;

    /**
     * @var Imap
     */
    protected $imap;

    /**
     * @var SearchStringManagerInterface
     */
    protected $searchStringManager;

    /**
     * @param ImapConfig $config
     * @param ImapServicesFactory $factory
     */
    public function __construct(ImapConfig $config, ImapServicesFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
        $this->imap = null;
    }

    /**
     * Gets the search query builder
     *
     * @return SearchQueryBuilder
     */
    public function getSearchQueryBuilder()
    {
        $this->ensureConnected();

        return new SearchQueryBuilder(new SearchQuery($this->searchStringManager));
    }

    /**
     * @param Folder|string|null $parentFolder
     * @param SearchQuery|null $query
     * @return Message[]
     */
    public function findItems($parentFolder = null, $query = null)
    {
        $this->ensureConnected();

        if ($parentFolder !== null) {
            $this->imap->selectFolder($parentFolder);
        }

        $searchString = '';
        if ($query !== null) {
            $searchString = $query->convertToSearchString();
        }
        if (empty($searchString)) {
            // Return all messages
            return iterator_to_array($this->imap);
        }

        $ids = $this->imap->search(array($searchString));

        $result = array();
        foreach ($ids as $messageId) {
            $result[] = $this->imap->getMessage($messageId);
        }

        return $result;
    }

    /**
     * Finds folders.
     *
     * @param string|null $parentFolder The global name of a parent folder.
     * @param bool $recursive Determines whether
     * @return Folder[]
     */
    public function findFolders($parentFolder = null, $recursive = false)
    {
        $this->ensureConnected();

        return $this->getSubFolders($this->imap->getFolders($parentFolder), $recursive);
    }

    /**
     * Finds a folder by its name.
     *
     * @param string $name The global name of the folder.
     * @return Folder
     */
    public function findFolder($name)
    {
        $this->ensureConnected();

        return $this->imap->getFolders($name);
    }

    /**
     * Retrieves item detail by its id.
     *
     * @param int $uid The UID of a message
     * @return Message
     */
    public function getItem($uid)
    {
        $this->ensureConnected();

        $id = $this->imap->getNumberByUniqueId($uid);

        return $this->imap->getMessage($id);
    }

    /**
     * Makes sure that there is active connection to IMAP server
     */
    protected function ensureConnected()
    {
        if ($this->imap === null) {
            $imapServices = $this->factory->createImapServices($this->config);
            $this->imap = $imapServices->getStorage();
            $this->searchStringManager = $imapServices->getSearchStringManager();
        }
    }

    /**
     * Gets sub folders.
     *
     * @param Folder $parentFolder The parent folder.
     * @param bool $recursive Determines whether
     * @return Folder[]
     */
    protected function getSubFolders(Folder $parentFolder, $recursive = false)
    {
        $result = array();
        foreach ($parentFolder as $folder) {
            $result[] = $folder;
            if ($recursive) {
                $result = array_merge($result, $this->getSubFolders($folder, $recursive));
            }
        }

        return $result;
    }
}
