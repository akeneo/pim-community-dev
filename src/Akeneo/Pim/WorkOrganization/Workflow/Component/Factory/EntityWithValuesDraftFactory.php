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

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

/**
 * EntityWithValues factory interface
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface EntityWithValuesDraftFactory
{
    /**
     * Creates an entity with values draft instance.
     * @param EntityWithValuesInterface $entityWithValues
     * @param DraftSource $draftSource
     * @return EntityWithValuesDraftInterface|null
     */
    public function createEntityWithValueDraft(EntityWithValuesInterface $entityWithValues, DraftSource $draftSource): ?EntityWithValuesDraftInterface;
}
