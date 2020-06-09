<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ProductConcatenateAction implements ProductConcatenateActionInterface
{
    /** @var ProductSourceCollection */
    private $sourceCollection;

    /** @var ProductTarget */
    private $target;

    public function __construct(array $data)
    {
        foreach (['from', 'to'] as $key) {
            Assert::keyExists($data, $key, sprintf('Concatenate configuration requires a "%s" key.', $key));
        }

        $this->sourceCollection = ProductSourceCollection::fromNormalized($data['from']);
        $this->target = ProductTarget::fromNormalized($data['to']);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceCollection(): ProductSourceCollection
    {
        return $this->sourceCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget(): ProductTarget
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields(): array
    {
        return [$this->target->getField()];
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'concatenate';
    }
}
