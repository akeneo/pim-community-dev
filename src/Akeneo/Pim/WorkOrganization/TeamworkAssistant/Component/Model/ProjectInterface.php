<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Symfony\Component\Security\Core\User\UserInterface;

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
    public function getCode();

    /**
     * @param string|ProjectIdentifier $code
     */
    public function setCode($code);

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
     * @return ChannelInterface
     */
    public function getChannel();

    /**
     * @param ChannelInterface $channel
     */
    public function setChannel(ChannelInterface $channel);

    /**
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * @param LocaleInterface $locale
     */
    public function setLocale(LocaleInterface $locale);

    /**
     * @return DatagridView
     */
    public function getDatagridView();

    /**
     * @param DatagridView $datagridView
     */
    public function setDatagridView(DatagridView $datagridView);

    /**
     * Add a new user GroupInterface to the Project.
     *
     * @param GroupInterface $group
     */
    public function addUserGroup(GroupInterface $group);

    /**
     * Remove a user GroupInterface to the Project.
     *
     * @param GroupInterface $group
     */
    public function removeUserGroup(GroupInterface $group);

    /**
     * Returns user groups.
     *
     * @return ArrayCollection $group
     */
    public function getUserGroups();


    /**
     * Reset the user groups
     */
    public function resetUserGroups();

    /**
     * Returns PQB filters in php array format.
     *
     * @return array $productFilters
     */
    public function getProductFilters();

    /**
     * @param array $productFilters
     */
    public function setProductFilters(array $productFilters);

    /**
     * @param ProjectStatusInterface $projectStatus
     */
    public function addProjectStatus(ProjectStatusInterface $projectStatus);

    /**
     * Reset the project statuses
     */
    public function resetProjectStatus();

    /**
     * @return ArrayCollection
     */
    public function getProjectStatus();

    public function startCompletenessComputing(): void;

    public function endCompletenessComputing(): void;
}
