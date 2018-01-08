<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\FamilyInterface;
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

    /**
     * @param ValidatorInterface          $validator
     * @param BulkSaverInterface          $familyVariantSaver
     * @param BulkObjectDetacherInterface $objectDetacher
     */
    public function __construct(
        ValidatorInterface $validator,
        BulkSaverInterface $familyVariantSaver,
        BulkObjectDetacherInterface $objectDetacher
    ) {
        $this->validator = $validator;
        $this->familyVariantSaver = $familyVariantSaver;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'validateAndSaveFamilyVariants',
        ];
    }

    /**
     * Validates and saves the family variants belonging to a family whenever it is updated.
     *
     * @param GenericEvent $event
     *
     * @throws \LogicException
     */
    public function validateAndSaveFamilyVariants(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof FamilyInterface) {
            return;
        }

        $validFamilyVariants = [];
        $allViolations = [];

        foreach ($subject->getFamilyVariants() as $familyVariant) {
            $violations = $this->validator->validate($familyVariant);

            if (0 === $violations->count()) {
                $validFamilyVariants[] = $familyVariant;
            } else {
                $allViolations[$familyVariant->getCode()] = $violations;
            }
        }

        $this->familyVariantSaver->saveAll($validFamilyVariants);
        $this->objectDetacher->detachAll($validFamilyVariants);

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
}
