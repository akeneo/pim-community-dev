<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamWorkAssistant\Factory;

use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectFactoryInterface
{
    /**
     * Build a project with its dependencies, project data look like :
     *
     * [
     *     'label' => 'Summer collection 2017',
     *     'due_date' => '2016-12-15',
     *     'description' => 'My description',
     *     'channel' => 'ecommerce',
     *     'locale' => 'fr_FR',
     *     'owner' => 'julia', *
     *     'datagrid_view' => [
     *          'filters' => 'i=1&p=10&s%5Bupdated',
     *          'columns' => 'sku,name,description',
     *     ],
     *     'product_filters' => [
     *          'field' => 'family',
     *          'operator' => 'IN',
     *          'value' => ['mugs'],
     *          'context' => ['locale' => 'en_US','scope' => 'ecommerce']
     *     ],
     * ]
     *
     * @param array $projectData
     *
     * @return ProjectInterface
     */
    public function create(array $projectData);
}
