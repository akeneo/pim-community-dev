<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch\Sorter;

use Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Exception\InvalidDirectionException;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

/**
 * InGroup sorter for an Elasticsearch query used for variant-group and group product grid
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InGroupSorter extends BaseFieldSorter implements FieldSorterInterface
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param array                    $supportedFields
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        array $supportedFields = []
    ) {
        parent::__construct($supportedFields);
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $channel = null)
    {
        $groupId = str_replace('in_group_', '', $field);

        $group = null;
        if (null !== $groupId) {
            $group = $this->groupRepository->find($groupId);
        }

        if (null === $group) {
            throw new InvalidArgumentException(
                self::class,
                sprintf('Unsupported field "%s" for InGroupSorter.', $field)
            );
        }

        $field = sprintf('%s.%s', 'in_group', $group->getCode());

        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the sorter.');
        }

        switch ($direction) {
            case Directions::ASCENDING:
                $sortClause = [
                    $field => [
                        'order'   => 'ASC',
                        'missing' => '_first',
                        'unmapped_type'=> 'boolean',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortClause);

                break;
            case Directions::DESCENDING:
                $sortClause = [
                    $field => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                        'unmapped_type'=> 'boolean',
                    ],
                ];
                $this->searchQueryBuilder->addSort($sortClause);

                break;
            default:
                throw InvalidDirectionException::notSupported($direction, static::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return (strpos($field, 'in_group_') !== false);
    }
}
