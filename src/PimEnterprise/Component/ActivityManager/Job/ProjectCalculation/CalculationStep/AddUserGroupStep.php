<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\AddUserGroupEngineInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * Find contributor groups (user groups which have edit on the product) affected by the project and
 * add them to the project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AddUserGroupStep implements CalculationStepInterface
{
    /** @var AddUserGroupEngineInterface */
    protected $addUserGroupEngine;

    /**
     * @param AddUserGroupEngineInterface $addUserGroupEngine
     */
    public function __construct(AddUserGroupEngineInterface $addUserGroupEngine)
    {
        $this->addUserGroupEngine = $addUserGroupEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $this->addUserGroupEngine->addUserGroup($project, $product);
    }
}
