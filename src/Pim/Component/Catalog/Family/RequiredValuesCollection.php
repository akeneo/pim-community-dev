<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Family;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Matrix of required attributes for a channel and a scope.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequiredValuesCollection
{
    /** @var ValueCollectionInterface */
    private $requiredValues;

    /**
     * @param ValueInterface $value
     */
    public function add(ValueInterface $value): void
    {
        $this->requiredValues->add($value);
    }
}
