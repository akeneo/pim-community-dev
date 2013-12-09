<?php

namespace Pim\Bundle\DemoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nelmio\Alice\Fixtures;

/**
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadDataCommand extends ContainerAwareCommand
{
    private $insertedNodesCount = 0;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:category:generate')
            ->addArgument('depth', InputArgument::OPTIONAL, 'Max depth of the tree', 5)
            ->addArgument(
                'nodes-per-level',
                InputArgument::OPTIONAL,
                'Number of nodes to add at each level'
                .'(i.e 10: lvl1 => 10 nodes, lvl2 => 20 nodes, lvl3 => 30 nodes, etc.',
                10
            )
            ->addArgument('max-products-per-node', InputArgument::OPTIONAL, 'Max number of products per node', 10)
            ->addOption('purge-table', null, InputOption::VALUE_NONE, 'Whether or not to purge db');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('purge-table')) {
            $output->write('Purging table...');
            $this->purgeTable();
            $output->writeln(' done');
        }

        $depth               = $input->getArgument('depth');
        $this->nodesPerLevel = $input->getArgument('nodes-per-level');
        $maxProductsPerNode  = $input->getArgument('max-products-per-node');
        for ($i = 1; $i < $depth; $i++) {
            $this->totalNodesCount += $this->nodesPerLevel * $i;
        }
        $this->totalNodesCount++;

        $output->writeln(
            sprintf(
                '<question>Generating a categories tree with the following characteristics:</question>'."\n".
                '<comment> > </comment> depth                           <info>%s</info>' . "\n" .
                '<comment> > </comment> nodes added at each level       <info>%s</info>' . "\n" .
                '<comment> > </comment> max products attached to a node <info>%s</info>' . "\n" .
                '<comment> > </comment> total number of generated nodes <info>%s</info>' . "\n",
                $depth,
                $this->nodesPerLevel,
                $maxProductsPerNode,
                $this->totalNodesCount
            )
        );

        $class = 'Pim\Bundle\CatalogBundle\Entity\Category';
        $fixtures[$class]['root'] = array(
            'code (unique)' => '<sentence(2)>',
            'title'         => '<sentence(2)>',
        );
        $nodesPerLevel = 0;
        for ($level = 0; $level < $depth - 1; $level++) {
            $nodesPerLevel += $this->nodesPerLevel;
            for ($i = 0; $i < $nodesPerLevel; $i++) {
                $fixtures[$class][sprintf('category-%d-%d', $level, $i)] = array(
                    'code (unique)' => '<sentence(2)>',
                    'title'         => '<sentence(2)>',
                    'parent'        => $level ? sprintf(
                        '@category-%d-%d',
                        $level - 1,
                        rand(0, $nodesPerLevel - $this->nodesPerLevel - 1)
                    ) : '@root',
                    'products'      => $this->getSomeRandomProducts($maxProductsPerNode),
                );
                $fixtures = $this->insert($fixtures, $class, $output);
            }
        }

        $this->insert($fixtures, $class, $output, true);
        $output->writeln(sprintf('<info>Tree with %d nodes created.</info>', $this->insertedNodesCount));
    }

    private function insert(array $fixtures, $class, OutputInterface $output, $force = false)
    {
        if (count($fixtures[$class]) >= 100 || $force) {
            $objects = Fixtures::load($fixtures, $this->getEntityManager());
            $this->insertedNodesCount += count($objects);

            $output->writeln(
                sprintf(
                    '<comment>  Inserted %d categories... %.1f%%</comment>',
                    $this->insertedNodesCount,
                    ($this->insertedNodesCount/$this->totalNodesCount) * 100
                )
            );

            return array();
        }

        return $fixtures;
    }

    private function getSomeRandomProducts($maxProductsPerNode)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('Pim\Bundle\CatalogBundle\Model\Product', 'u');
        $query = $em->createNativeQuery(
            sprintf(
                'SELECT * FROM pim_product order by rand() limit %d',
                rand(0, $maxProductsPerNode)
            ),
            $rsm
        );

        return $query->getResult();
    }

    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    private function purgeTable()
    {
        $em = $this->getEntityManager();
        $em->getConnection()->exec('DELETE FROM pim_category_translation');
        $em->getConnection()->exec('DELETE FROM pim_category');
    }
}
