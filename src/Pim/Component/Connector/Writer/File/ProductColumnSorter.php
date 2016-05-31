<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
    /** @var FieldSplitter */
    protected $fieldSplitter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var array */
    protected $firstDefaultColumns;

    /**
     * @param FieldSplitter $fieldSplitter
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param array $firstDefaultColumns
     */
    public function __construct(
        FieldSplitter $fieldSplitter,
        IdentifiableObjectRepositoryInterface $productRepository,
        array $firstDefaultColumns
    ) {
        $this->fieldSplitter = $fieldSplitter;
        $this->productRepository = $productRepository;
        parent::__construct($fieldSplitter, $firstDefaultColumns);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(array $columns)
    {
        $identifier = $this->productRepository->getIdentifierProperties()[0];
        array_unshift($this->firstDefaultColumns, $identifier);

        return parent::sort($columns);
    }
}
