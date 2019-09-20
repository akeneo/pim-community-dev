<?php

namespace Akeneo\Pim\Structure\Bundle\ReferenceData\Command;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\ReferenceDataUniqueCodeChecker;
use Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\CheckerInterface;
use Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\ReferenceDataInterfaceChecker;
use Akeneo\Pim\Structure\Bundle\ReferenceData\RequirementChecker\ReferenceDataNameChecker;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks if a reference data is correctly configured.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckRequirementsCommand extends Command
{
    protected static $defaultName = 'pim:reference-data:check';

    /** @var ConfigurationRegistryInterface */
    private $configurationRegistry;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $referenceDataInterface;

    public function __construct(
        ConfigurationRegistryInterface $configurationRegistry,
        ObjectManager $entityManager,
        string $referenceDataInterface
    ) {
        parent::__construct();
        $this->configurationRegistry = $configurationRegistry;
        $this->entityManager = $entityManager;
        $this->referenceDataInterface = $referenceDataInterface;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Check the requirements of the reference data configuration');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->configurationRegistry->all() as $configuration) {
            $output->writeln('');
            $output->writeln(
                sprintf('<comment>Checking configuration of "%s"...</comment>', $configuration->getName())
            );

            foreach ($this->getCheckers() as $checker) {
                $this->checkConfiguration($checker, $configuration, $output);
            }
        }
    }

    /**
     * @return CheckerInterface[]
     */
    protected function getCheckers()
    {
        $checkers = [];
        $checkers[] = new ReferenceDataNameChecker();
        $checkers[] = new ReferenceDataInterfaceChecker($this->referenceDataInterface);
        $checkers[] = new ReferenceDataUniqueCodeChecker($this->entityManager);

        return $checkers;
    }

    /**
     * @param CheckerInterface                    $checker
     * @param ReferenceDataConfigurationInterface $configuration
     * @param OutputInterface                     $output
     */
    protected function checkConfiguration(
        CheckerInterface $checker,
        ReferenceDataConfigurationInterface $configuration,
        OutputInterface $output
    ) {
        if ($checker->check($configuration)) {
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
}
