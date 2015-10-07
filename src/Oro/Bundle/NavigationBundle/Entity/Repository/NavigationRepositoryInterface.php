<?php

namespace Oro\Bundle\NavigationBundle\Entity\Repository;

/**
 * NavigationItem Repository interface
 */
interface NavigationRepositoryInterface
{
    /**
     * Find all navigation items for specified user
     *
     * @param \Pim\Bundle\UserBundle\Entity\UserInterface $user
     * @param string                                      $type
     * @param array                                       $options
     *
     * @return array
     */
    public function getNavigationItems($user, $type, $options = array());
}
