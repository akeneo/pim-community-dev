<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Command;

use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Upgrades\AddIndexOnRuleDefinitionCodeQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddIndexOnRuleDefinitionCodeCommand extends Command
{
    protected static $defaultName = 'akeneo:rule:upgrade-index';

    /** @var AddIndexOnRuleDefinitionCodeQuery */
    private $addIndexQuery;

    public function __construct(AddIndexOnRuleDefinitionCodeQuery $addIndexQuery)
    {
        parent::__construct();
        $this->addIndexQuery = $addIndexQuery;
    }

    protected function configure(): void
    {
        $this->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addIndexQuery->execute();
    }
}
