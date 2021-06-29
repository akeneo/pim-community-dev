<?php

namespace Akeneo\Pim\Structure\Component\Reader\Database\MassEdit;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredFamilyReader implements ItemReaderInterface, StepExecutionAwareInterface, InitializableInterface, TrackableItemReaderInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ArrayCollection */
    protected $families;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var bool */
    private $firstRead = true;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    public function totalItems(): int
    {
        $filters = $this->getConfiguredFilters();
        $familyIds = $this->getFamilyIds($filters);

        return count($familyIds);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->families) {
            $filters = $this->getConfiguredFilters();
            $this->families = $this->getFamilies($filters);
        }

        if (false === $this->firstRead) {
            $this->families->next();
        }

        $family = $this->families->current();
        $this->firstRead = false;

        if (null !== $family) {
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $family;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function initialize()
    {
        $filters = $this->getConfiguredFilters();
        $this->families = $this->getFamilies($filters);
        $this->firstRead = true;
    }

    /**
     * Get families with given $filters.
     * In this particular case, we'll only have 1 filter based on ids
     * (We don't have raw filters yet for family grid)
     *
     * @param array $filters
     *
     * @return \Generator
     */
    protected function getFamilies(array $filters)
    {
        $familiesIds = $this->getFamilyIds($filters);

        foreach ($familiesIds as $familyId) {
            $family = $this->familyRepository->find($familyId);

            if (null !== $family) {
                yield $family;
            }
        }
    }

    /**
     * @return array
     */
    protected function getConfiguredFilters()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return $jobParameters->get('filters');
    }

    protected function getFamilyIds(array $filters)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);

        $filter = current($filters);
        $filter = $resolver->resolve($filter);

        return $filter['value'];
    }
}
