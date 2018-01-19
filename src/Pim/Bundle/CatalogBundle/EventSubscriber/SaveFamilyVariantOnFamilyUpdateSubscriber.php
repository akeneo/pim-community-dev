<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validates and saves the family variants belonging to a family whenever it is updated.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveFamilyVariantOnFamilyUpdateSubscriber implements EventSubscriberInterface
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var BulkSaverInterface */
    private $familyVariantSaver;

    /** @var BulkObjectDetacherInterface */
    private $objectDetacher;

    /** @var BulkSaverInterface */
    private $bulkfamilyVariantSaver;

    /**
     * @param ValidatorInterface          $validator
     * @param SaverInterface              $familyVariantSaver
     * @param BulkSaverInterface          $bulkFamilyVariantSaver
     * @param BulkObjectDetacherInterface $objectDetacher
     */
    public function __construct(
        ValidatorInterface $validator,
        SaverInterface $familyVariantSaver,
        BulkSaverInterface $bulkFamilyVariantSaver = null,
        BulkObjectDetacherInterface $objectDetacher = null
    ) {
        $this->validator = $validator;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->objectDetacher = $objectDetacher;
        $this->bulkfamilyVariantSaver = $bulkFamilyVariantSaver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'onUnitarySave',
            StorageEvents::POST_SAVE_ALL => 'onBulkSave',
        ];
    }

    /**
     * Validates and saves the family variants belonging to a family whenever it is updated.
     *
     * By explicitly calling the `FamilyVariantSaver::save` function we ensure that the:
     * 1. `compute_family_variant_structure_changes` job will run
     * 2. `compute_product_model_descendants` job will run.
     *
     * hence, updating the catalog asynchronously.
     *
     * @param GenericEvent $event
     *
     * @throws \LogicException
     */
    public function onUnitarySave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof FamilyInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $validationResponse = $this->validateFamilyVariants($subject);
        $validFamilyVariants = $validationResponse['valid_family_variants'];
        $allViolations = $validationResponse['violations'];

        foreach ($validFamilyVariants as $familyVariant) {
            $this->familyVariantSaver->save($familyVariant);
        }
        if (null !== $this->objectDetacher) {
            $this->objectDetacher->detachAll($validFamilyVariants);
        }

        if (!empty($allViolations)) {
            $errorMessage = $this->getErrorMessage($allViolations);
            throw new \LogicException($errorMessage);
        }
    }

    /**
     * Validates and saves the family variants belonging to a family whenever it is updated.
     *
     * By explicitly calling the `FamilyVariantSaver::saveAll` function we ensure there will be no background job run to
     * update the variant product and product model related to the family variant (for scalability reasons).
     *
     * The update of the product models and variant products should be done in a dedicated component such as an import
     * step.
     *
     * @param GenericEvent $event
     */
    public function onBulkSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof FamilyInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || true === $event->getArgument('unitary')) {
            return;
        }

        $validationResponse = $this->validateFamilyVariants($subject);
        $validFamilyVariants = $validationResponse['valid_family_variants'];
        $allViolations = $validationResponse['violations'];

        if (null !== $this->bulkfamilyVariantSaver) {
            $this->bulkfamilyVariantSaver->saveAll($validFamilyVariants);
        }
        if (null !== $this->objectDetacher) {
            $this->objectDetacher->detachAll($validFamilyVariants);
        }

        if (!empty($allViolations)) {
            $errorMessage = $this->getErrorMessage($allViolations);
            throw new \LogicException($errorMessage);
        }
    }

    /**
     * Formats an error message with all the given ConstraintViolationLists
     *
     * @param array $allViolations
     *
     * @return string
     */
    private function getErrorMessage(array $allViolations): string
    {
        $errorMessage = 'One or more errors occured while updating the following family variants:\n';
        foreach ($allViolations as $familyVariantCode => $constraintViolationList) {
            $errorMessage .= sprintf('%s:\n', $familyVariantCode);
            foreach ($constraintViolationList as $violation) {
                $errorMessage .= sprintf('- %s\n', $violation->getMessage());
            }
        }

        return $errorMessage;
    }

    /**
     * @param FamilyInterface $family
     *
     * @return FamilyVariantInterface[]
     */
    private function validateFamilyVariants(FamilyInterface $family): array
    {
        $validFamilyVariants = [];
        $allViolations = [];

        foreach ($family->getFamilyVariants() as $familyVariant) {
            $violations = $this->validator->validate($familyVariant);

            if (0 === $violations->count()) {
                $validFamilyVariants[] = $familyVariant;
            } else {
                $allViolations[$familyVariant->getCode()] = $violations;
            }
        }

        return ['valid_family_variants' => $validFamilyVariants, 'violations' => $allViolations];
    }
}
