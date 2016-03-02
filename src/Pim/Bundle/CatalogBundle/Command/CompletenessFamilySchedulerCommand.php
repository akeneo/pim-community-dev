<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reschedule completeness for family command
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class CompletenessFamilySchedulerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:completeness:schedule-family')
            ->setDescription('Schedule completeness calculation for the specified family')
            ->addArgument('family', InputArgument::REQUIRED, 'The family code');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $familyCode = $input->getArgument('family');

        $family = $this->findFamily($familyCode);
        if (null === $family) {
            $output->writeln(
                sprintf('<error>Family "%s" not found</error>', $familyCode)
            );

            return 1;
        }

        $this->getCompletenessGenerator()->scheduleForFamily($family);

        return 0;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository
     */
    protected function getFamilyRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.family');
    }

    /**
     * @param string $familyCode
     *
     * @return null|object
     */
    protected function findFamily($familyCode)
    {
        return $this->getFamilyRepository()->findOneByIdentifier($familyCode);
    }

    /**
     * @return CompletenessGeneratorInterface
     */
    protected function getCompletenessGenerator()
    {
        return $this->getContainer()->get('pim_catalog.doctrine.completeness_generator');
    }
}
