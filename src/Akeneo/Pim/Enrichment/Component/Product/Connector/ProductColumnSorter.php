<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter;

/**
 * Reorder columns before export
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductColumnSorter extends DefaultColumnSorter implements ColumnSorterInterface
{
    private ?string $identifierAttributeCode = null;

    public function __construct(
        FieldSplitter $fieldSplitter,
        protected AttributeRepositoryInterface $attributeRepository,
        protected AssociationTypeRepositoryInterface $associationTypeRepository,
        array $firstDefaultColumns
    ) {
        parent::__construct($fieldSplitter, $firstDefaultColumns);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(array $columns, array $context = [])
    {
        if (isset($context['filters']['structure']['attributes']) &&
            !empty($context['filters']['structure']['attributes'])
        ) {
            $rawColumns = array_merge(
                ['uuid'],
                [$this->identifierAttributeCode()],
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

        return parent::sort($columns);
    }

    protected function getFirstDefaultColumns(): array
    {
        return \array_merge(['uuid', $this->identifierAttributeCode()], $this->firstDefaultColumns);
    }

    private function identifierAttributeCode(): string
    {
        if (null === $this->identifierAttributeCode) {
            $this->identifierAttributeCode = $this->attributeRepository->getIdentifierCode();
        }

        return $this->identifierAttributeCode;
    }
}
