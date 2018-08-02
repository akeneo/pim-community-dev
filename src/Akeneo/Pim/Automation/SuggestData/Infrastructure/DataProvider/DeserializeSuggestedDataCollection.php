<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider;

/**
 * Deserialize HAL suggested data to SuggestedDataCollectionInterface
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class DeserializeSuggestedDataCollection
{
    /** @var SuggestedDataFactory */
    protected $suggestedDataFactory;

    /**
     * @param SuggestedDataFactory $suggestedDataFactory
     */
    public function __construct(SuggestedDataFactory $suggestedDataFactory)
    {
        $this->suggestedDataFactory = $suggestedDataFactory;
    }

    /**
     * HAL suggested data to SuggestedDataCollectionInterface.
     *
     * @param array $data
     *
     * @return SuggestedDataCollectionInterface
     */
    public function deserialize(array $data): SuggestedDataCollectionInterface
    {
        $this->validateData($data);

        $suggestedDataCollection = new SuggestedDataCollection();
        foreach ($data['_embedded']['product'] as $product) {
            $suggestedData = $this->suggestedDataFactory->create(
                $product['id'],
                $product['codes'],
                $product['attributes']
            );
            $suggestedDataCollection->add($suggestedData);
        }

        return $suggestedDataCollection;
    }

    /**
     * @param array $data
     *
     * @throws \InvalidArgumentException
     */
    private function validateData(array $data): void
    {
        if (!array_key_exists('_embedded', $data)) {
            throw new \InvalidArgumentException(); // TODO write error message
        }

        if (!array_key_exists('product', $data['_embedded'])) {
            throw new \InvalidArgumentException(); // TODO write error message
        }

        foreach ($data['_embedded']['product'] as $product) {
            if (!array_key_exists('id', $product) ||
                !array_key_exists('codes', $product) ||
                !array_key_exists('attributes', $product)
            ) {
                throw new \InvalidArgumentException(); // TODO write error message
            }
        }
    }
}
