<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Builder;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;

/**
 * EntityWithValues draft builder interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface EntityWithValuesDraftBuilderInterface
{
    /**
     * @throws \LogicException
     *
     * @return EntityWithValuesDraftInterface|null returns null if no draft is created
     */
    public function build(EntityWithValuesInterface $entityWithValues, string $username): ?EntityWithValuesDraftInterface;
}
