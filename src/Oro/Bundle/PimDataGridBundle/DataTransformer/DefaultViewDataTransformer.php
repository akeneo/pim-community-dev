<?php

namespace Oro\Bundle\PimDataGridBundle\DataTransformer;

use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform the getDefaultDatagridViews into User's properties
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultViewDataTransformer implements DataTransformerInterface
{
    /** @var DatagridViewRepositoryInterface */
    protected $datagridViewRepo;

    /**
     * @param DatagridViewRepositoryInterface $datagridViewRepo
     */
    public function __construct(DatagridViewRepositoryInterface $datagridViewRepo)
    {
        $this->datagridViewRepo = $datagridViewRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return;
        }

        $types = $this->datagridViewRepo->getDatagridViewTypeByUser($value);
        foreach ($types as $type) {
            $field = 'default_' . str_replace('-', '_', $type['datagridAlias']) . '_view';
            $value->$field = $value->getDefaultGridView($type['datagridAlias']);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        $types = $this->datagridViewRepo->getDatagridViewTypeByUser($value);
        foreach ($types as $type) {
            $field = 'default_' . str_replace('-', '_', $type['datagridAlias']) . '_view';

            if (property_exists($value, $field)) {
                if ($value->getDefaultGridView($type['datagridAlias']) !== $value->$field) {
                    $value->setDefaultGridView($type['datagridAlias'], $value->$field);
                }
            }
        }

        return $value;
    }
}
