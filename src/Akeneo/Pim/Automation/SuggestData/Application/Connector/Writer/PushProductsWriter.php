<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Application\Connector\Writer;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataCollection;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;

/**
 * Writer to push products to PIM.ai.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class PushProductsWriter implements ItemWriterInterface, FlushableInterface
{
    /** @var DataProviderInterface */
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
