<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory as SingleValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueFactory
{
    /** @var array|SingleValueFactory[] */
    private $valueFactories;

    public function __construct(iterable $valueFactories)
    {
        Assert::allIsInstanceOf($valueFactories, SingleValueFactory::class);

        /** @var ValueFactory $readValueFactory */
        foreach ($valueFactories as $readValueFactory) {
            $this->valueFactories[$readValueFactory->supportedAttributeType()] = $readValueFactory;
        }
    }

    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        return $this->getFactory($attribute)->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (null === $data || [] === $data || '' === $data || [''] === $data || [null] === $data) {
            throw new InvalidArgumentException(get_class($this), sprintf('Data should not be empty, %s found', json_encode($data)));
        }

        return $this->getFactory($attribute)->createByCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    private function getFactory(Attribute $attribute): SingleValueFactory
    {
        if (isset($this->valueFactories[$attribute->type()])) {
            return $this->valueFactories[$attribute->type()];
        }

        throw new \OutOfBoundsException(sprintf(
            'No read factory has been registered to create a Product Value for the attribute type "%s"',
            $attribute->type()
        ));
    }
}
