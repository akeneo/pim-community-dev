<?php


namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Command;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Services\EntityContextProvider;
use Akeneo\Tool\Component\DatabaseMetadata\BatchEntityValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatabaseBatchValidatorCommand extends Command
{
    protected static $defaultName = 'pimee:database:batch-validate';

    private array $baseContext = [];
    private array $batchValidators = [];
    private LoggerInterface $logger;
    private EntityContextProvider $contextProvider;

    public function __construct(iterable $batchValidators, LoggerInterface $logger, EntityContextProvider $contextProvider)
    {
        parent::__construct();
        $this->batchValidators = iterator_to_array($batchValidators);
        $this->logger = $logger;
        $this->contextProvider = $contextProvider;
        $baseContext['cmd_class'] = get_class($this);
    }

    public function remaining(\IteratorAggregate $generator)
    {
        yield from $generator;
    }


    protected function configure()
    {
        $this
            ->setDescription("This command performs database batch validation checks.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $executionStatus = self::SUCCESS;

        $this->logger->notice("Starting database validation", $this->baseContext);
        $constraintViolationLists = [];

        /** @var BatchEntityValidator $batchValidator */
        foreach ($this->batchValidators as $batchValidator) {
            $constraintViolationLists[get_class($batchValidator->getJobInstanceRepository())] = $batchValidator->validateAll();
        }
        foreach ($constraintViolationLists as $batchValidator => $constraintViolationLists) {
            $this->logger->notice("Inspecting source: {$batchValidator}");
            /** @var ConstraintViolationList $constraintViolationList */
            foreach ($constraintViolationLists as $constraintViolationList) {
                /** @var ConstraintViolation $constraintViolation */
                foreach ($constraintViolationList as $constraintViolation) {
                    $executionStatus = self::FAILURE;
                    $rootEntity = $constraintViolation->getRoot();
                    $context = array_merge($this->baseContext, $this->contextProvider->mapEntity2LogContext($rootEntity));
                    $context['validator'] = $batchValidator;
                    $context['contraint'] = get_class($constraintViolation->getConstraint());
                    $context['message_template'] = $constraintViolation->getMessageTemplate();
                    $context['message_parameters'] = $constraintViolation->getParameters();
                    $context['property_path'] = $constraintViolation->getPropertyPath();

                    $this->logger->error($constraintViolation->getMessage(), $context);
                }
            }
        }
        $this->logger->notice("Ending database validation", $this->baseContext);
        return (int) $executionStatus;
    }
}
