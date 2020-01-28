<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

/**
 * EntityWithValues draft builder interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface EntityWithValuesDraftBuilderInterface
{
    /**
     * @param EntityWithValuesInterface $entityWithValues
     * @param DraftSource $draftSource
     * @return EntityWithValuesDraftInterface|null returns null if no draft is created
     */
    public function build(EntityWithValuesInterface $entityWithValues, DraftSource $draftSource): ?EntityWithValuesDraftInterface;
}
