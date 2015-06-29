<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Exception;

/**
 * Exception raised when attempting to mass-review a draft while the user can't (permissions issues typically)
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class DraftNotReviewableException extends \Exception
{
}
