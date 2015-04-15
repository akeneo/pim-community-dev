<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Job configuration manager
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditConfigurationManager
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var string*/
    protected $massEditJobConfigurationClass;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /**
     * @param ObjectManager                  $objectManager
     * @param SavingOptionsResolverInterface $optionsResolver
     * @param string                         $massEditJobConfigurationClass
     */
    function __construct(ObjectManager $objectManager, SavingOptionsResolverInterface $optionsResolver, $massEditJobConfigurationClass)
    {
        $this->objectManager                 = $objectManager;
        $this->massEditJobConfigurationClass = $massEditJobConfigurationClass;
        $this->optionsResolver               = $optionsResolver;
    }

    /**
     * @param JobExecution $jobExecution
     * @param string       $configuration
     */
    public function create(JobExecution $jobExecution, $configuration)
    {
        $massEditJobConf = new $this->massEditJobConfigurationClass($jobExecution, $configuration);

        $this->objectManager->persist($massEditJobConf);
        $this->objectManager->flush($massEditJobConf);
    }
}
