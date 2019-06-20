<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityItemsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityItem;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindReferenceEntityItems implements FindReferenceEntityItemsInterface
{
    /** @var ReferenceEntityItem[] */
    private $results = [];

    public function save(ReferenceEntityItem $referenceEntityDetails)
    {
        $this->results[] = $referenceEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function find(): array
    {
        return $this->results;
    }
}
