<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Bundle\WorkflowBundle\Datagrid\Normalizer\ProductProposalNormalizer;

/**
 * Hydrator for product draft (ORM support)
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductDraftHydrator implements HydratorInterface
{
    /** @var RequestParametersExtractorInterface */
    protected $extractor;

    /** @var ProductProposalNormalizer */
    protected $normalizer;

    /**
     * @param RequestParametersExtractorInterface $extractor
     * @param ProductProposalNormalizer           $normalizer
     */
    public function __construct(
        RequestParametersExtractorInterface $extractor,
        ProductProposalNormalizer $normalizer
    ) {
        $this->extractor = $extractor;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $locale = $this->extractor->getParameter('dataLocale');
        $records = [];
        foreach ($qb->getQuery()->execute() as $result) {
            $result = current($result);
            if ($result->hasChanges()) {
                $result->setDataLocale($locale);
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
