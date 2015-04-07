<?php

namespace Pim\Bundle\ReferenceDataBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\ReferenceDataBundle\RequirementChecker\CheckerInterface;
use Pim\Bundle\ReferenceDataBundle\RequirementChecker\ProductValueAccessorsChecker;
use Pim\Bundle\ReferenceDataBundle\RequirementChecker\ProductValueRelationshipChecker;
use Pim\Bundle\ReferenceDataBundle\RequirementChecker\ReferenceDataInterfaceChecker;
use Pim\Bundle\ReferenceDataBundle\RequirementChecker\ReferenceDataNameChecker;
use Pim\Bundle\ReferenceDataBundle\RequirementChecker\ReferenceDataUniqueCodeChecker;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks if a reference data is correctly configured.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckRequirementsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:reference-data:check')
            ->setDescription('Check the requirements of the reference data configuration');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getRegistry()->all() as $configuration) {
            $output->writeln('');
            $output->writeln(sprintf('<comment>Checking configuration of "%s"...</comment>', $configuration->getName()));

            $this->checkConfiguration(new ReferenceDataNameChecker(), $configuration, $output);
            $this->checkConfiguration(new ReferenceDataInterfaceChecker(), $configuration, $output);
            $this->checkConfiguration(
                new ReferenceDataUniqueCodeChecker($this->getDoctrineProductManager()),
                $configuration,
                $output
            );
            $this->checkConfiguration(
                new ProductValueAccessorsChecker($this->getProductValueClass()),
                $configuration,
                $output
            );
            $this->checkConfiguration(
                new ProductValueRelationshipChecker($this->getDoctrineProductManager(), $this->getProductValueClass()),
                $configuration,
                $output
            );
        }
    }

    /**
     * @param CheckerInterface       $checker
     * @param ConfigurationInterface $configuration
     * @param OutputInterface        $output
     */
    protected function checkConfiguration(
        CheckerInterface $checker,
        ConfigurationInterface $configuration,
        OutputInterface $output
    ) {
        if (true === $checker->check($configuration)) {
            $output->write('<info>[OK]</info>    ');
            $output->writeln($checker->getDescription());
        } else {
            $output->write('<error>[KO]</error>    ');
            $output->writeln($checker->getDescription());
            $output->writeln(sprintf('<error>%s</error>', $checker->getFailure()));

            if ($checker->isBlockingOnFailure()) {
                exit(-1);
            }
        }
    }

    /**
     * @return ConfigurationRegistryInterface
     */
    protected function getRegistry()
    {
        return $this->getContainer()->get('pim_reference_data.registry');
    }

    /**
     * @return ObjectManager
     */
    protected function getDoctrineProductManager()
    {
        return $this->getContainer()->get('pim_catalog.object_manager.product');
    }

    /**
     * @return string
     */
    protected function getProductValueClass()
    {
        return $this->getContainer()->getParameter('pim_catalog.entity.product_value.class');
    }
}
