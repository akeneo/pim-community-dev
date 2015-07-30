<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Family import processor
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $familyConverter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var FamilyFactory */
    protected $familyFactory;

    /**
     * @param StandardArrayConverterInterface       $familyConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param FamilyFactory                         $familyFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $familyConverter,
        FamilyFactory $familyFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->arrayConverter = $familyConverter;
        $this->familyFactory  = $familyFactory;
        $this->updater        = $updater;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $family = $this->findOrCreateFamily($convertedItem);

        try {
            $this->updateFamily($family, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateFamily($family);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $family;
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
     * @return FamilyInterface
     */
    protected function findOrCreateFamily(array $convertedItem)
    {
        $family = $this->findObject($this->repository, $convertedItem);
        if (null === $family) {
            return $this->familyFactory->createFamily();
        }

        return $family;
    }

    /**
     * @param FamilyInterface $family
     * @param array           $convertedItem
     *
     * @throws \InvalidArgumentException
     */
    protected function updateFamily(FamilyInterface $family, array $convertedItem)
    {
        $this->updater->update($family, $convertedItem);
    }

    /**
     * @param FamilyInterface $family
     *
     * @throws \InvalidArgumentException
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateFamily(FamilyInterface $family)
    {
        return $this->validator->validate($family);
    }
}
