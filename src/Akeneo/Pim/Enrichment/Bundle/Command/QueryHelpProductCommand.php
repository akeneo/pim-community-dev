<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helps to query products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryHelpProductCommand extends Command
{
    protected static $defaultName = 'pim:product:query-help';

    /** @var DumperInterface */
    private $fieldDumper;

    /** @var DumperInterface */
    private $attributeDumper;

    public function __construct(
        DumperInterface $fieldDumper,
        DumperInterface $attributeDumper
    ) {
        parent::__construct();

        $this->fieldDumper = $fieldDumper;
        $this->attributeDumper = $attributeDumper;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Display useable product query filters');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fieldDumper->dump($output);
        $this->attributeDumper->dump($output);
    }
}
