<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Connector\Reader\MassEdit;

use Pim\Component\Connector\Reader\Database\AbstractReader;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Reader able to read filters from the Product Assets grid and fetch assets,
 * used for mass edit operations for example.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilteredAssetReader extends AbstractReader
{
    /** @var AssetRepositoryInterface */
    protected $repository;

    /**
     * @param AssetRepositoryInterface $repository
     */
    public function __construct(AssetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     *
     * In this particular case, we'll only have 1 filter based on ids
     * (We don't have raw filters yet for asset grid)
     */
    protected function getResults()
    {
        $filters = $this->getConfiguredFilters();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);

        $filter = current($filters);
        $filter = $resolver->resolve($filter);

        $assetIds = $filter['value'];

        return new \ArrayIterator($this->repository->findByIds($assetIds));
    }

    /**
     * @return array|null
     */
    protected function getConfiguredFilters()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return $jobParameters->get('filters');
    }
}
