<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

/**
 * Family saver, contains custom logic for family's product saving
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySaver implements SaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessSavingOptionsResolver */
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
    public function save($family, array $options = [])
    {
        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\FamilyInterface", "%s" provided.',
                    ClassUtils::getClass($family)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($family);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
        if (true === $options['schedule']) {
            $this->completenessManager->scheduleForFamily($family);
        }
    }
}
