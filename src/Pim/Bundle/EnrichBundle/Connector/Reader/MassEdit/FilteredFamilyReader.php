<?php

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredFamilyReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool */
    protected $isExecuted;

    /** @var ArrayCollection */
    protected $families;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /**
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     * @param FamilyRepositoryInterface           $familyRepository
     */
    public function __construct(
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->jobConfigurationRepo = $jobConfigurationRepo;
        $this->familyRepository     = $familyRepository;

        $this->isExecuted = false;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $configuration = $this->getJobConfiguration();

        if (!$this->isExecuted) {
            $this->isExecuted = true;
            $this->families = $this->getFamilies($configuration['filters']);
        }

        $result = $this->families->current();

        if (!empty($result)) {
            $this->stepExecution->incrementSummaryInfo('read');
            $this->families->next();
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Get families with given $filters.
     * In this particular case, we'll only have 1 filter based on ids
     * (We don't have raw filters yet for family grid)
     *
     * @param array $filters
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getFamilies(array $filters)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);

        $filter = current($filters);
        $filter = $resolver->resolve($filter);

        $familiesIds = $filter['value'];

        return new ArrayCollection($this->familyRepository->findByIds($familiesIds));
    }

    /**
     * Return the job configuration
     *
     * @throws EntityNotFoundException
     *
     * @return array
     */
    protected function getJobConfiguration()
    {
        $jobExecution    = $this->stepExecution->getJobExecution();
        $massEditJobConf = $this->jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution]);

        if (null === $massEditJobConf) {
            throw new EntityNotFoundException(sprintf(
                'No JobConfiguration found for jobExecution with id %s',
                $jobExecution->getId()
            ));
        }

        return json_decode(stripcslashes($massEditJobConf->getConfiguration()), true);
    }
}
