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
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
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
    public function getCode();

    /**
     * @param string $code
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
    public function setDueDate(\DateTime $dueDate);

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
     * @return ArrayCollection $group
     */
    public function getUserGroups();

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
     * @param ProductInterface $product
     */
    public function addProduct(ProductInterface $product);

    /**
     * @param ArrayCollection $products
     */
    public function setProducts(ArrayCollection $products);

    /**
     * @return ArrayCollection
     */
    public function getProducts();
}
