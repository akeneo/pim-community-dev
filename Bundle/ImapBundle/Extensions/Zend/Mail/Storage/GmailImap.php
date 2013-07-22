<?php

namespace Oro\Bundle\ImapBundle\Extensions\Zend\Mail\Storage;

class GmailImap extends Imap
{
    const DEFAULT_GMAIL_HOST = 'imap.gmail.com';
    const DEFAULT_GMAIL_PORT = '993';
    const DEFAULT_GMAIL_SSL = 'ssl';

    const X_GM_MSGID = 'X-GM-MSGID';
    const X_GM_THRID = 'X-GM-THRID';
    const X_GM_LABELS = 'X-GM-LABELS';

    /**
     * {@inheritdoc}
     */
    public function __construct($params)
    {
        parent::__construct($params);
        array_push($this->getMessageItems, self::X_GM_MSGID, self::X_GM_THRID, self::X_GM_LABELS);

    }

    /**
     * {@inheritdoc}
     */
    public function search(array $criteria)
    {
        if (!empty($criteria)) {
            $lastItem = end($criteria);
            if (strpos($lastItem, '"') === 0 && substr($lastItem, -strlen('"')) === '"') {
                array_unshift($criteria, 'X-GM-RAW');
            }
        }

        return parent::search($criteria);
    }

    /**
     * {@inheritdoc}
     */
    protected function setExtHeaders(&$headers, array $data)
    {
        parent::setExtHeaders($headers, $data);

        $headers->addHeaderLine(self::X_GM_MSGID, $data[self::X_GM_MSGID]);
        $headers->addHeaderLine(self::X_GM_THRID, $data[self::X_GM_THRID]);
        $headers->addHeaderLine(
            self::X_GM_LABELS,
            isset($data[self::X_GM_LABELS]) ? $data[self::X_GM_LABELS] : array()
        );
    }
}
