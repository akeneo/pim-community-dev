<?php

namespace Oro\Bundle\ImapBundle\Mail\Storage;

use Zend\Mail\Storage\Folder as BaseFolder;

class Folder extends BaseFolder
{
    const FLAG_SENT = 'Sent';
    const FLAG_SPAM = 'Spam';
    const FLAG_TRASH = 'Trash';
    const FLAG_DRAFTS = 'Drafts';
    const FLAG_INBOX = 'Inbox';

    /**
     * @var string[]
     */
    protected $flags;

    /**
     * Determines whether this folder is marked by the given flag
     *
     * @param string $flagName
     * @return bool
     */
    public function hasFlag($flagName)
    {
        if (empty($this->flags)) {
            return false;
        }

        return in_array('\\' . $flagName, $this->flags);
    }

    /**
     * Sets flags
     *
     * @param string[] $flags
     */
    public function setFlags(array $flags)
    {
        $this->flags = $flags;
    }
}
