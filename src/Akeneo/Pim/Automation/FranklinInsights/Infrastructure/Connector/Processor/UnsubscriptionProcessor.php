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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class UnsubscriptionProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        return $product;
    }
}
