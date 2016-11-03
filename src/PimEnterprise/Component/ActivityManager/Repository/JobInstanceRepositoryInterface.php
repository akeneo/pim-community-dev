<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Repository;

use Akeneo\Component\Batch\Model\JobInstance;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface JobInstanceRepositoryInterface
{
    /**
     * Return the project calculation job instance.
     *
     * @return JobInstance
     */
    public function getProjectCalculation();
}
