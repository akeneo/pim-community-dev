<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     */
    public function setDescription($description);

    /**
     * @return \DateTime
     */
    public function getDueDate();

    /**
     * @param \DateTime $dueDate
     *
     * @return
     */
    public function setDueDate(\DateTime $dueDate = null);

    /**
     * @return UserInterface
     */
    public function getOwner();

    /**
     * @param UserInterface $owner
     */
    public function setOwner(UserInterface $owner);

    /**
     * @return ArrayCollection
     */
    public function getDatagridViews();

    /**
     * @param DatagridView $datagridView
     */
    public function addDatagridView(DatagridView $datagridView);

    /**
     * @param DatagridView $datagridView
     */
    public function removeDatagridView(DatagridView $datagridView);

    /**
     * Add a new user group to the Project.
     *
     * @param Group $group
     */
    public function addUserGroup(Group $group);

    /**
     * Remove a user group to the Project.
     *
     * @param Group $group
     */
    public function removeUserGroup(Group $group);

    /**
     * Returns user groups.
     *
     * @return Group $group
     */
    public function getUserGroups();

    /**
     * Returns product filters
     *
     * @return string $productFilters
     */
    public function getProductFilters();

    /**
     * @param string $productFilters
     */
    public function setProductFilters($productFilters);
}
