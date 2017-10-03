<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantDatasource extends RepositoryDatasource
{
    /** @var NormalizerInterface */
    protected $normalizer;

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
    public function getResults()
    {
        $familyVariants = $this->qb->getQuery()->execute();

        return array_map(function ($familyVariant) {
            return new ResultRecord(
                $this->normalizer->normalize(
                    $familyVariant,
                    'datagrid',
                    ['localeCode' => $this->getParameters()[':localeCode']]
                )
            );
        }, $familyVariants);
    }
}
