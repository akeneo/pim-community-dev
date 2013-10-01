<?php

namespace Oro\Bundle\ImapBundle\Manager\DTO;

class ItemId
{
    /**
     * @var int
     */
    private $uid;

    /**
     * @var int
     */
    private $uidValidity;

    /**
     * Constructor
     *
     * @param int $uid
     * @param int $uidValidity
     */
    public function __construct($uid, $uidValidity)
    {
        $this->uid = $uid;
        $this->uidValidity = $uidValidity;
    }

    /**
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     * @return $this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return int
     */
    public function getUidValidity()
    {
        return $this->uidValidity;
    }

    /**
     * @param int $uidValidity
     * @return $this
     */
    public function setUidValidity($uidValidity)
    {
        $this->uidValidity = $uidValidity;

        return $this;
    }
}
