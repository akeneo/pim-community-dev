<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;

/**
 * Represents an email owner
 */
interface EmailOwnerInterface extends FirstNameInterface, LastNameInterface
{
    /**
     * Get entity class name.
     * TODO: This is a temporary solution for get 'view' route in twig.
     *       Will be removed after EntityConfigBundle is finished
     *
     * @return string
     */
    public function getClass();

    /**
     * Get name of field contains the primary email address
     *
     * @return string
     */
    public function getPrimaryEmailField();

    /**
     * Get entity unique id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName();
}
