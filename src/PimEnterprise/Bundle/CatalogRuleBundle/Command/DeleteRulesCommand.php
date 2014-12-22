<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to generate fake rules
 *
 * @TODO: the command should ask (y/n) if you want do delete rule, --force option to not ask, and add a rule in parameter to delete only one rule
 * @TODO: delete this test command ?
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DeleteRulesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:rule-dev:delete');
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $rules = $this->getContainer()->get('pimee_rule_engine.repository.rule')->findAll();
        foreach ($rules as $rule) {
            $em->remove($rule);
        }
        $em->flush();
    }
}
