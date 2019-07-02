<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * When executing a rule:
 * - it selects first all the products or product models where the rule will be applied
 * - it applies the rule
 * - it saves the product or the products model which triggers the indexation
 *
 * Then, it executes the next rule.
 *
 * As the indexation is asynchronous, the next rule have to wait the refresh of the indexation to be sure to select the
 * correct product or product models, that could have been modified by the previous rule.
 */
final class RefreshIndexesBeforeRuleSelectionSubscriber implements EventSubscriberInterface
{
    /** @var Client */
    private $productClient;

    /** @var Client */
    private $productAndProductModelClient;

    /** @var Client */
    private $productModelClient;

    public function __construct(Client $productClient, Client $productAndProductModelClient, Client $productModelClient)
    {
        $this->productClient = $productClient;
        $this->productAndProductModelClient = $productAndProductModelClient;
        $this->productModelClient = $productModelClient;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RuleEvents::PRE_SELECT => 'refreshIndexes',
        ];
    }

    public function refreshIndexes(RuleEvent $event): void
    {
        $this->productClient->refreshIndex();
        $this->productAndProductModelClient->refreshIndex();
        $this->productModelClient->refreshIndex();
    }
}
