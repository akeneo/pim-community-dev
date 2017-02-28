<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Factory;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\DatagridViewTypes;
use PimEnterprise\Component\TeamworkAssistant\Factory\ProjectFactoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectFactory implements ProjectFactoryInterface
{
    /** @var SimpleFactoryInterface */
    protected $datagridViewFactory;

    /** @var SimpleFactoryInterface */
    protected $projectUpdater;

    /** @var SimpleFactoryInterface */
    protected $datagridViewUpdater;

    /** @var string */
    protected $projectClassname;

    /**
     * @param ObjectUpdaterInterface $projectUpdater
     * @param SimpleFactoryInterface $datagridViewFactory
     * @param ObjectUpdaterInterface $datagridViewUpdater
     * @param string                 $projectClassName
     */
    public function __construct(
        ObjectUpdaterInterface $projectUpdater,
        SimpleFactoryInterface $datagridViewFactory,
        ObjectUpdaterInterface $datagridViewUpdater,
        $projectClassName
    ) {
        $this->projectUpdater = $projectUpdater;
        $this->datagridViewFactory = $datagridViewFactory;
        $this->datagridViewUpdater = $datagridViewUpdater;
        $this->projectClassname = $projectClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $projectData)
    {
        $datagridViewData = $projectData['datagrid_view'];
        $datagridViewData['type'] = DatagridViewTypes::PROJECT_VIEW;
        $datagridViewData['owner'] = $projectData['owner'];
        $datagridViewData['datagrid_alias'] = 'product-grid';

        $datagridView = $this->datagridViewFactory->create();
        $this->datagridViewUpdater->update($datagridView, $datagridViewData);

        $projectData['datagrid_view'] = $datagridView;

        $project = new $this->projectClassname();
        $this->projectUpdater->update($project, $projectData);

        return $project;
    }
}
