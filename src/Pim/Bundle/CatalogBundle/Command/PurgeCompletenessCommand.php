<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Purge the completeness of the products family by family: rows from the table "pim_catalog_completeness" will be deleted
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeCompletenessCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:completeness:purge')
            ->setDescription('Purge the product completenesses');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qb = $this->getFamilyQueryBuilder();
        $paginator = new Paginator($qb);

        foreach ($paginator as $family) {
            $output->writeln(sprintf('Purging completenesses of the family "%s"...', $family->getCode()));
            $this->getCompletenessManager()->scheduleForFamily($family);
        }

        $output->writeln('<info>Completenesses purged.</info>');
    }

    /**
     * @return CompletenessManager
     */
    protected function getCompletenessManager()
    {
        return $this
            ->getContainer()
            ->get('pim_catalog.manager.completeness');
    }

    /**
     * @return QueryBuilder
     */
    protected function getFamilyQueryBuilder()
    {
        $em = $this->getEntityManager();

        return $em->createQueryBuilder()
            ->select('f')
            ->from($this->getFamilyClass(), 'f');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return string
     */
    protected function getFamilyClass()
    {
        return $this->getContainer()->getParameter('pim_catalog.entity.family.class');
    }
}
