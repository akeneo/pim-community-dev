<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource\ResultRecord;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Hydrator for product draft
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductDraftHydrator implements HydratorInterface
{
    /** @var RequestParametersExtractorInterface */
    protected $extractor;

    /** @var NormalizerInterface */
    protected $normalizer;

    public function __construct(
        RequestParametersExtractorInterface $extractor,
        NormalizerInterface $normalizer
    ) {
        $this->extractor = $extractor;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $records = [];
        foreach ($qb->getQuery()->execute() as $result) {
            $result = current($result);
            if ($result->hasChanges()) {
                $normalizedItem = $this->normalizeEntityWithValues($result);
                $record = new ResultRecord($normalizedItem);
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * @param EntityWithValuesInterface $item
     *
     * @return array
     */
    private function normalizeEntityWithValues(EntityWithValuesInterface $item): array
    {
        $defaultNormalizedItem = [
            'id'            => $item->getId(),
            'categories'    => null,
            'values'        => [],
            'created'       => null,
            'updated'       => null,
            'label'         => null,
            'changes'       => null,
            'document_type' => null,
        ];

        $normalizedItem = array_merge(
            $defaultNormalizedItem,
            $this->normalizer->normalize($item, 'datagrid')
        );

        return $normalizedItem;
    }
}
