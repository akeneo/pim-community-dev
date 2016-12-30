<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Builder;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Datagrid\DatagridViewTypes;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectBuilder implements ProjectBuilderInterface
{
    /** @var SimpleFactoryInterface */
    protected $datagridViewFactory;

    /** @var SimpleFactoryInterface */
    protected $projectFactory;

    /** @var SimpleFactoryInterface */
    protected $projectUpdater;

    /** @var SimpleFactoryInterface */
    protected $datagridViewUpdater;

    /**
     * @param SimpleFactoryInterface $projectFactory
     * @param ObjectUpdaterInterface $projectUpdater
     * @param SimpleFactoryInterface $datagridViewFactory
     * @param ObjectUpdaterInterface $datagridViewUpdater
     */
    public function __construct(
        SimpleFactoryInterface $projectFactory,
        ObjectUpdaterInterface $projectUpdater,
        SimpleFactoryInterface $datagridViewFactory,
        ObjectUpdaterInterface $datagridViewUpdater
    ) {
        $this->projectFactory = $projectFactory;
        $this->projectUpdater = $projectUpdater;
        $this->datagridViewFactory = $datagridViewFactory;
        $this->datagridViewUpdater = $datagridViewUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $projectData)
    {
        $datagridViewData = $projectData['datagrid_view'];
        $datagridViewData['type'] = DatagridViewTypes::PROJECT_VIEW;
        $datagridViewData['owner'] = $projectData['owner'];
        $datagridViewData['datagrid_alias'] = 'product-grid';

        $datagridView = $this->datagridViewFactory->create();
        $this->datagridViewUpdater->update($datagridView, $datagridViewData);

        $projectData['datagrid_view'] = $datagridView;

        $project = $this->projectFactory->create();
        $this->projectUpdater->update($project, $projectData);

        return $project;
    }
}
