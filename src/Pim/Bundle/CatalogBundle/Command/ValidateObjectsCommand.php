<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Model\ReferableInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Validates all objects except products
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidateObjectsCommand extends ContainerAwareCommand
{
    /** @var array */
    protected $errors = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:objects:validate')
            ->setDescription('Load and validate all objects except products');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateObjects($this->getService('pim_catalog.repository.association_type'), 'ASSOCIATION TYPES');
        $this->validateObjects($this->getService('pim_catalog.repository.attribute'), 'ATTRIBUTES');
        $this->validateObjects($this->getService('pim_catalog.repository.family'), 'FAMILIES');
        $this->validateObjects($this->getService('pim_catalog.repository.group_type'), 'GROUP TYPES');
        $this->validateObjects($this->getService('pim_catalog.repository.group'), 'GROUP');
        $this->validateObjects($this->getService('pim_catalog.repository.channel'), 'CHANNEL');
        $this->validateObjects($this->getService('pim_catalog.repository.category'), 'CATEGORY');

        if (!empty($this->errors)) {
            $this->displayErrors($output);

            return -1;
        }

        $output->writeln('<info>No error has been detected.</info>');

        return 0;
    }

    /**
     * @param ObjectRepository $repository The entity repository
     * @param string           $type       Type is used to display errors if needed
     */
    protected function validateObjects(ObjectRepository $repository, $type)
    {
        $validator = $this->getService('validator');
        $entities  = $repository->findAll();

        foreach ($entities as $entity) {
            $violations = $validator->validate($entity);

            if (0 !== $violations->count()) {
                $this->addErrors($violations, $entity, $type);
            }
        }
    }

    /**
     * @param ConstraintViolationList $violations
     * @param ReferableInterface      $entity
     * @param string                  $type
     */
    protected function addErrors(ConstraintViolationList $violations, ReferableInterface $entity, $type)
    {
        $this->initializeErrorsStorage($entity->getReference(), $type);

        foreach ($violations as $violation) {
            $this->errors[$type][$entity->getReference()][] = $violation->getMessage();
        }
    }

    /**
     * @param string $entityReference
     * @param string $type
     */
    protected function initializeErrorsStorage($entityReference, $type)
    {
        if (!isset($this->errors[$type][$entityReference])) {
            $this->errors[$type][$entityReference] = [];
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function displayErrors(OutputInterface $output)
    {
        foreach ($this->errors as $type => $entities) {
            $output->writeln(sprintf('<comment>%s</comment>', $type));

            foreach ($entities as $entity => $errors) {
                foreach ($errors as $error) {
                    $output->writeln(sprintf('<error>%s : "%s"</error>', $entity, $error));
                }
            }
        }
    }

    /**
     * @param string $serviceName
     *
     * @return object
     */
    protected function getService($serviceName)
    {
        return $this->getContainer()->get($serviceName);
    }
}
