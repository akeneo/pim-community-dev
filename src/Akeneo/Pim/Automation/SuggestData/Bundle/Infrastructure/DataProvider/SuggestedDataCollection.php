<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

/**
 * Represents a collection of suggested data from PIM.ai
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SuggestedDataCollection implements SuggestedDataCollectionInterface
{
    /** @var SuggestedDataInterface[] */
    private $suggestedData;

    /** @var int */
    private $position;

    /**
     * @param SuggestedDataInterface[] $suggestedData Array of SuggestedDataInterface
     */
    public function __construct(array $suggestedData = [])
    {
        $this->validateData($suggestedData);

        $this->position = 0;
        $this->suggestedData = array_values($suggestedData);
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function current(): SuggestedDataInterface
    {
        return $this->suggestedData[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return $this->position < count($this->suggestedData);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->suggestedData);
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->suggestedData);
    }

    /**
     * {@inheritdoc}
     */
    public function add(SuggestedDataInterface $suggestedData): void
    {
        $this->suggestedData[] = $suggestedData;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(int $index): bool
    {
        if (!array_key_exists($index, $this->suggestedData)) {
            return false;
        }

        unset($this->suggestedData[$index]);

        return true;
    }

    /**
     * @param array $suggestedData
     *
     * @throws \InvalidArgumentException
     */
    private function validateData(array $suggestedData): void
    {
        foreach ($suggestedData as $object) {
            if (!$object instanceof SuggestedDataInterface) {
                throw new \InvalidArgumentException(
                    'All values of given array must be an instance of SuggestedDataInterface.' .
                    'TODO write a better error message.'
                );
            }
        }
    }
}
