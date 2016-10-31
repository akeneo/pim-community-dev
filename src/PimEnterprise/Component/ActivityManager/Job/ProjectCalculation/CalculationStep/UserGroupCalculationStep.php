<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class UserGroupCalculationStep implements CalculationStepInterface
{
    /** @var ObjectUpdaterInterface  */
    private $objectUpdater;

    public function __construct(ObjectUpdaterInterface $objectUpdater)
    {
        $this->objectUpdater = $objectUpdater;
    }

    public function execute(ProductInterface $product, ProjectInterface $project)
    {

    }
}
