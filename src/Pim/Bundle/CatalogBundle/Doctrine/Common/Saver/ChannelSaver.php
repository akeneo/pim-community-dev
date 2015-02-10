<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

/**
 * Channel saver, contains custom logic for channel saving
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelSaver implements SaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var CompletenessSavingOptionsResolver */
    protected $optionsResolver;

    /**
     * @param ObjectManager                     $objectManager
     * @param CompletenessManager               $completenessManager
     * @param CompletenessSavingOptionsResolver $optionsResolver
     */
    public function __construct(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        CompletenessSavingOptionsResolver $optionsResolver
    ) {
        $this->objectManager       = $objectManager;
        $this->completenessManager = $completenessManager;
        $this->optionsResolver     = $optionsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($channel, array $options = [])
    {
        if (!$channel instanceof ChannelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\ChannelInterface", "%s" provided.',
                    get_class($channel)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($channel);
        if (true === $options['schedule']) {
            $this->completenessManager->scheduleForChannel($channel);
        }
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
