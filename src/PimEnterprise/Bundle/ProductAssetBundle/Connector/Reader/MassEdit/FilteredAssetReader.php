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

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
class FilteredAssetReader implements ItemReaderInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var AssetRepositoryInterface */
    private $repository;

    /** @var ArrayCollection */
    private $assets;

    /**
     * @param AssetRepositoryInterface $repository
     */
    public function __construct(AssetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->assets) {
            $this->assets = $this->getAssets();
        } else {
            $this->assets->next();
        }

        $asset = $this->assets->current();

        if (null !== $asset) {
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $asset;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function getAssets()
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);

        $filter = current($this->stepExecution->getJobParameters()->get('filters'));
        $filter = $resolver->resolve($filter);

        $assetIds = $filter['value'];

        foreach ($assetIds as $assetId) {
            $asset = $this->repository->find($assetId);

            if (null !== $asset) {
                yield $asset;
            }
        }
    }
}
