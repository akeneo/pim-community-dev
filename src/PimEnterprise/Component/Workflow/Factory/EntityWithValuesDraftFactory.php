<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Factory;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;

/**
 * EntityWithValues factory interface
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface EntityWithValuesDraftFactory
{
    /**
     * Creates an entity with values draft instance.
     */
    public function createEntityWithValueDraft(EntityWithValuesInterface $enityWithValues, string $username): ?EntityWithValuesDraftInterface;
}
