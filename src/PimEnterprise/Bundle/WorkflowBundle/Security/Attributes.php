<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Security;

/**
 * Security voter attributes for workflow specific permissions
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class Attributes
{
    const FULL_REVIEW = 'FULL_REVIEW';

    const PARTIAL_REVIEW = 'PARTIAL_REVIEW';
}
