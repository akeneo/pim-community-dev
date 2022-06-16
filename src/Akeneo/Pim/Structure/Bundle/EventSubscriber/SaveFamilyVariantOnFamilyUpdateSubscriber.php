<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Validates and saves the family variants belonging to a family whenever it is updated.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveFamilyVariantOnFamilyUpdateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private BulkSaverInterface $bulkFamilyVariantSaver
    ) {
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
     * As we are not in an import context, the `compute_family_variant_structure_changes` job is triggered
     * by the bulkFamilyVariantSaver->saveAll(). We force the launch of this job because if an attribute
     * at the common level is removed, the family variant stays unchanged but we need to recompute the root/sub
     * product models.
     *
     * hence, updating the catalog asynchronously.
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

        Assert::isArray($validFamilyVariants);
        $this->bulkFamilyVariantSaver->saveAll($validFamilyVariants, [
            ComputeFamilyVariantStructureChangesSubscriber::FORCE_JOB_LAUNCHING => true,
        ]);

        if (!empty($allViolations)) {
            $errorMessage = $this->getErrorMessage($allViolations);
            throw new \LogicException($errorMessage);
        }
    }

    /**
     * Validates and saves the family variants belonging to a family whenever it is updated.
     *
     * When the family variant are saved, we disable the launch of the 'compute_family_variant_structure_changes' job
     * because the compute is already done in a dedicated job of the import.
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

        $this->bulkFamilyVariantSaver->saveAll(
            $validFamilyVariants,
            [ComputeFamilyVariantStructureChangesSubscriber::DISABLE_JOB_LAUNCHING => true]
        );

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
        $errorMessage = 'One or more errors occurred while updating the following family variants:\n';
        foreach ($allViolations as $familyVariantCode => $constraintViolationList) {
            $errorMessage .= sprintf('%s:\n', $familyVariantCode);
            foreach ($constraintViolationList as $violation) {
                $errorMessage .= sprintf('- %s\n', $violation->getMessage());
            }
        }

        return $errorMessage;
    }

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
