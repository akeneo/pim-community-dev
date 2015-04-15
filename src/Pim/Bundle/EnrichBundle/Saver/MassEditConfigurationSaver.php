<?php

namespace Pim\Bundle\EnrichBundle\Saver;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration;

/**
 * Job configuration saver
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditConfigurationSaver implements SaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /**
     * @param ObjectManager                  $objectManager
     * @param SavingOptionsResolverInterface $optionsResolver
     */
    function __construct(ObjectManager $objectManager, SavingOptionsResolverInterface $optionsResolver)
    {
        $this->objectManager   = $objectManager;
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($massEditJobConf, array $options = [])
    {
        if (!$massEditJobConf instanceof MassEditJobConfiguration) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration", "%s" provided.',
                    get_class($massEditJobConf)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($massEditJobConf);

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
