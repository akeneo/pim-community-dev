<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Datagrid;

use Pim\Bundle\DataGridBundle\Entity\DatagridView;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
final class DatagridViewTypes
{
    /**
     * Default view type, the datagrid view is visible by everyone.
     */
    const PUBLIC_VIEW = DatagridView::TYPE_PUBLIC;

    /**
     * Datagrid view linked to a project, does not appear in the UI.
     */
    const PROJECT_VIEW = 'project';
}
