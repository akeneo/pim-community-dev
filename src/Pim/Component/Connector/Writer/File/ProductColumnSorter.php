<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

/**
 * Reorder columns before export
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductColumnSorter extends DefaultColumnSorter implements ColumnSorterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepository;

    /**
     * @param FieldSplitter                         $fieldSplitter
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param AssociationTypeRepositoryInterface    $associationTypeRepository
     * @param array                                 $firstDefaultColumns
     */
    public function __construct(
        FieldSplitter $fieldSplitter,
        IdentifiableObjectRepositoryInterface $productRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        array $firstDefaultColumns
    ) {
        parent::__construct($fieldSplitter, $firstDefaultColumns);

        $this->productRepository         = $productRepository;
        $this->associationTypeRepository = $associationTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(array $columns, array $context = [])
    {
        $identifier = $this->productRepository->getIdentifierProperties()[0];

        if (isset($context['filters']['structure']['attributes']) &&
            !empty($context['filters']['structure']['attributes'])
        ) {
            $rawColumns = array_merge(
                [$identifier],
                $this->firstDefaultColumns,
                array_map(function ($associationType) {
                    return $associationType->getCode();
                }, $this->associationTypeRepository->findAll()),
                $context['filters']['structure']['attributes']
            );

            $sortedColumns = [];
            foreach ($rawColumns as $columnCode) {
                $sortedColumns = array_merge($sortedColumns, array_filter($columns, function ($columnCandidate) use ($columnCode) {
                    return 0 !== preg_match(sprintf('/^%s(-.*)*$/', $columnCode), $columnCandidate);
                }));
            }

            return array_unique($sortedColumns);
        }

        array_unshift($this->firstDefaultColumns, $identifier);

        return parent::sort($columns);
    }
}
