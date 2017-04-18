<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch\Sorter;

use Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
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
        parent::addFieldSorter($field, $direction, $locale, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return (strpos($field, 'in_group_') !== false);
    }
}
