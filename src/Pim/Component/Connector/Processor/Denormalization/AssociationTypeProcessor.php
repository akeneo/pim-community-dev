<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Factory\AssociationTypeFactory;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * AssociationType import processor
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $arrayConverter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var AssociationTypeFactory */
    protected $associationTypeFactory;

    /**
     * @param StandardArrayConverterInterface       $arrayConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param AssociationTypeFactory                $associationTypeFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        AssociationTypeFactory $associationTypeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->arrayConverter          = $arrayConverter;
        $this->associationTypeFactory  = $associationTypeFactory;
        $this->updater                 = $updater;
        $this->validator               = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $associationType = $this->findOrCreateAssociationType($convertedItem);

        try {
            $this->updateAssociationType($associationType, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateAssociationType($associationType);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $associationType;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->arrayConverter->convert($item);
    }

    /**
     * @param array $convertedItem
     *
     * @return AssociationTypeInterface
     */
    protected function findOrCreateAssociationType(array $convertedItem)
    {
        $associationType = $this->findObject($this->repository, $convertedItem);
        if (null === $associationType) {
            return $this->associationTypeFactory->createAssociationType();
        }

        return $associationType;
    }

    /**
     * @param AssociationTypeInterface $associationType
     * @param array                    $convertedItem
     *
     * @throws \InvalidArgumentException
     */
    protected function updateAssociationType(AssociationTypeInterface $associationType, array $convertedItem)
    {
        $this->updater->update($associationType, $convertedItem);
    }

    /**
     * @param AssociationTypeInterface $associationType
     *
     * @throws \InvalidArgumentException
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateAssociationType(AssociationTypeInterface $associationType)
    {
        return $this->validator->validate($associationType);
    }
}
