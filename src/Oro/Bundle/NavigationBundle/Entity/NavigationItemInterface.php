<?php

namespace Oro\Bundle\NavigationBundle\Entity;

/**
 * Navigation Entity Interface
 */
interface NavigationItemInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get user
     *
     * @return \Pim\Bundle\UserBundle\Entity\UserInterface
     */
    public function getUser();

    /**
     * Set entity properties
     *
     * @param array $values
     */
    public function setValues(array $values);
}
