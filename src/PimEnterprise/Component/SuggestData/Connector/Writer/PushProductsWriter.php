<?php
declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Connector\Writer;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\DataProviderFactory;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataCollection;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataCollectionInterface;

/**
 * Writer to push products to PIM.ai.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class PushProductsWriter implements ItemWriterInterface, FlushableInterface
{
    /** @var DataProviderAdapterInterface */
    protected $dataProvider;

    /** @var int */
    private $batchSize;

    /** @var array */
    private $productsToPush;

    /** @var SuggestedDataCollection */
    private $providedData;

    /**
     * @param DataProviderFactory $factory
     * @param int                 $batchSize
     */
    public function __construct(DataProviderFactory $factory, int $batchSize)
    {
        $this->dataProvider = $factory->create();
        $this->batchSize = $batchSize;
        $this->productsToPush = [];
        $this->providedData = new SuggestedDataCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->productsToPush[] = $item;
            if (0 === count($this->productsToPush) % $this->batchSize) {
                $suggestedDataCollection = $this->dataProvider->bulkPush($this->productsToPush);

                $this->save($suggestedDataCollection);
                $this->productsToPush = [];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        if (!empty($this->productsToPush)) {
            $suggestedDataCollection = $this->dataProvider->bulkPush($this->productsToPush);

            $this->save($suggestedDataCollection);
            $this->productsToPush = [];
        }

        var_dump($this->providedData);
    }


    private function save(SuggestedDataCollectionInterface $suggestedDataCollection): void
    {
        foreach ($suggestedDataCollection as $suggestedData) {
            $this->providedData->add($suggestedData);
        }
    }
}
