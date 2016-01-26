<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\Memory\HumanReadableBytesFormatter;
use Akeneo\Component\Memory\MemoryUsageProvider;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Purge all empty product values, depending on the attribute type, the value is considered empty on different criteria
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEmptyProductValuesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:purge-empty-values')
            ->setDescription('Purge all empty product values, please dump your database first!')
            ->addOption('memory-usage', null, InputOption::VALUE_NONE, 'Display the memory usage');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');
        $question = 'This command will remove empty product values, you must do a database backup before to perform it'
            . '. To avoid memory leak related to Symfony profiler keeping references on objects, you should use the '
            . ' --env=prod argument, it does not mean directly on your production database! Please notice that this '
            . 'command only supports native attribute types and can remove unexpected data in case of custom projects. '
            . 'Are you sure to run the command?';
        if (!$dialog->askConfirmation($output, sprintf('<question>%s</question>', $question), false)) {
            return;
        }

        $memoryProvider = new MemoryUsageProvider();
        $memoryFormatter = new HumanReadableBytesFormatter();

        $products = $this->getAllProducts();
        foreach ($products as $product) {
            $purgedProduct = $this->removeProductValues($product);
            if (true === $purgedProduct) {
                $violations = $this->validate($product);
                foreach ($violations as $violation) {
                    $output->writeln(sprintf("<error>%s</error>", $violation->getMessage()));
                }
                $identifier = $product->getIdentifier()->getData();
                if (0 === $violations->count()) {
                    $this->save($product);
                    $output->writeln(sprintf('<info>Product "%s" has been cleaned up</info>', $identifier));
                } else {
                    $output->writeln(sprintf('<error>Once purged the product "%s" is not valid</error>', $identifier));
                }
            }
            $this->detach($product);

            $displayMemoryUsage = $input->getOption('memory-usage');
            if ($displayMemoryUsage) {
                $output->writeln(
                    sprintf(
                        'usage: %s peak: %s / limit: %s',
                        $memoryFormatter->format($memoryProvider->getUsage()),
                        $memoryFormatter->format($memoryProvider->getPeakUsage()),
                        $memoryFormatter->format($memoryProvider->getLimit())
                    )
                );
            }
        }
        $output->writeln(sprintf('<info>Empty product values has been purged from your database</info>'));

        return 0;
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool has removed values
     */
    protected function removeProductValues(ProductInterface $product)
    {
        $emptyValuesRemover = $this->getContainer()->get('pim_catalog.updater.product_purger');

        return $emptyValuesRemover->removeEmptyProductValues($product);
    }

    /**
     * @return CursorInterface
     */
    protected function getAllProducts()
    {
        $factory = $this->getContainer()->get('pim_catalog.query.product_query_builder_factory');
        $productQueryBuilder = $factory->create();

        return $productQueryBuilder->execute();
    }

    /**
     * @param ProductInterface $product
     *
     * @return ConstraintViolationListInterface
     */
    protected function validate(ProductInterface $product)
    {
        $validator = $this->getContainer()->get('pim_validator');
        $errors = $validator->validate($product);

        return $errors;
    }

    /**
     * @param ProductInterface $product
     */
    protected function save(ProductInterface $product)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->save($product);
    }

    /**
     * @param ProductInterface $product
     */
    protected function detach(ProductInterface $product)
    {
        $detacher = $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher');
        $detacher->detach($product);
    }
}
