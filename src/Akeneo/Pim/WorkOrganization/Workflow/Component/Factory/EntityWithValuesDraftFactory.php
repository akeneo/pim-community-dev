<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Factory;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

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
