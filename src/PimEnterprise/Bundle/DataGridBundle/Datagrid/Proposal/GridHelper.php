<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * Helper for proposal datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var UserManager $userManager */
    protected $userManager;

    /**
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }
    /**
     * Returns available proposal author choices
     *
     * @return array
     */
    public function getAuthorChoices()
    {
        $users = $this->userManager->getRepository()->findAll();

        $choices = [];

        foreach ($users as $user) {
            $choices[$user->getUsername()] = $user->getUsername();
        }

        return $choices;
    }
}
