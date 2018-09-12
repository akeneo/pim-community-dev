<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantDatasource extends RepositoryDatasource
{
    /** @var NormalizerInterface */
    private $normalizer;

    /**
     * @param DatagridRepositoryInterface $repository
     * @param HydratorInterface           $hydrator
     * @param NormalizerInterface         $normalizer
     */
    public function __construct(
        DatagridRepositoryInterface $repository,
        HydratorInterface $hydrator,
        NormalizerInterface $normalizer
    ) {
        parent::__construct($repository, $hydrator);

        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(): array
    {
        $familyVariants = $this->qb->getQuery()->execute();

        return array_map(function ($familyVariant) {
            return new ResultRecord(
                $this->normalizer->normalize(
                    $familyVariant,
                    'datagrid',
                    ['localeCode' => isset($this->getParameters()[':localeCode']) ?
                        $this->getParameters()[':localeCode'] :
                        ''
                    ]
                )
            );
        }, $familyVariants);
    }
}
