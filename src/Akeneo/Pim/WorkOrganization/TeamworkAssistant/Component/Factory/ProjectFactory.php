<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Factory;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\DatagridViewTypes;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectFactory implements ProjectFactoryInterface
{
    protected SimpleFactoryInterface $datagridViewFactory;
    protected ObjectUpdaterInterface $projectUpdater;
    protected ObjectUpdaterInterface $datagridViewUpdater;

    /** @var string */
    protected $projectClassname;

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
