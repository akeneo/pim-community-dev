<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class OptionsMapping implements \IteratorAggregate
{
    /** @var OptionMapping[] */
    private $options = [];

    /**
     * @param array $optionsData
     */
    public function __construct(array $optionsData)
    {
        foreach ($optionsData as $optionData) {
            $this->options[] = new OptionMapping($optionData);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->options);
    }
}
