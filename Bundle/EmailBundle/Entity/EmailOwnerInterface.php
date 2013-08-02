<?php

namespace Oro\Bundle\EmailBundle\Entity;

/**
 * Represents an email owner
 */
interface EmailOwnerInterface
{
    /**
     * Get entity class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Get entity unique id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get full name of email owner according to the given name format
     *
     * @param  string $format
     * @return string
     */
    public function getFullname($format = '');

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();
}
