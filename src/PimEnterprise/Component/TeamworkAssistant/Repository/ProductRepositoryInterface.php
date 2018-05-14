<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Repository;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProductRepositoryInterface
{
    /**
     * @param ProjectInterface $project
     *
     * @return CursorInterface
     */
    public function findByProject(ProjectInterface $project);
}
