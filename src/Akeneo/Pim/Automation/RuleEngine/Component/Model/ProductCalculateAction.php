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

final class ProductCalculateAction implements ProductCalculateActionInterface, FieldImpactActionInterface
{
    /** @var ProductTarget */
    private $destination;

    /** @var Operand */
    private $source;

    /** @var OperationList */
    private $operationList;

    public function __construct(array $data)
    {
        Assert::keyExists($data, 'destination', 'The calculate action expects a "destination" key');
        $this->destination = ProductTarget::fromNormalized($data['destination']);

        Assert::keyExists($data, 'source', 'The calculate action expects a "source" key');
        $this->source = Operand::fromNormalized($data['source']);

        Assert::keyExists($data, 'operation_list', 'The calculate action expects an "operation_list" key');
        Assert::isArray($data['operation_list']);
        $this->operationList = OperationList::fromNormalized($data['operation_list']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDestination(): ProductTarget
    {
        return $this->destination;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(): Operand
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationList(): OperationList
    {
        return $this->operationList;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields()
    {
        return [$this->destination->getField()];
    }
}
