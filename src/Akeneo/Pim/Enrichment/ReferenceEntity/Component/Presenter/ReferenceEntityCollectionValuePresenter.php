<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Presenter;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\AbstractProductValuePresenter;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

/**
 * Present reference entity collection
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceEntityCollectionValuePresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION  === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($recordCollection)
    {
        return array_map(function (RecordCode $recordCode) {
            return $recordCode->normalize();
        }, $recordCollection);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $change['data'];
    }
}
