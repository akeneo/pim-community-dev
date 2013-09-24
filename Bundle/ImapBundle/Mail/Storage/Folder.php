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
    const FLAG_ALL = 'All';

    /**
     * @var string[]
     */
    public $flags = null;

    /**
     * Determines whether this folder is marked by the given flag
     *
     * @param string $flag
     * @return bool
     */
    public function hasFlag($flag)
    {
        if (empty($this->flags)) {
            return false;
        }

        if (!(strpos($flag, '\\') === 0)) {
            $flag = '\\' . $flag;
        }

        return in_array($flag, $this->flags);
    }

    /**
     * Sets flags
     *
     * @param string[] $flags
     */
    public function setFlags(array $flags)
    {
        if ($this->flags === null) {
            $this->flags = $flags;
        } else {
            foreach ($flags as $flag) {
                if (!in_array($flag, $this->flags)) {
                    $this->flags[] = $flag;
                }
            }
        }
    }

    /**
     * Adds a flag
     *
     * @param string $flag
     */
    public function addFlag($flag)
    {
        if ($this->flags === null) {
            $this->flags = array();
        }
        if (!(strpos($flag, '\\') === 0)) {
            $flag = '\\' . $flag;
        }
        if (!in_array($flag, $this->flags)) {
            $this->flags[] = $flag;
        }
    }

    /**
     * Deletes a flag
     *
     * @param string $flag
     */
    public function deleteFlag($flag)
    {
        if ($this->flags !== null) {
            if (!(strpos($flag, '\\') === 0)) {
                $flag = '\\' . $flag;
            }
            unset($this->flags[$flag]);
        }
    }
}
