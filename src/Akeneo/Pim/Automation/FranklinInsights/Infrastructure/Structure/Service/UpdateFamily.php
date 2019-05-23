<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\UpdateFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateFamily implements UpdateFamilyInterface
{
    /**
     * @var FamilyUpdater
     */
    private $updater;
    /**
     * @var FamilySaver
     */
    private $saver;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var FamilyRepositoryInterface
     */
    private $repository;

    /**
     * UpdateFamily constructor.
     * @param FamilyUpdater $updater
     * @param FamilySaver $saver
     * @param FamilyRepository $repository
     * @param ValidatorInterface $validator
     */
    public function __construct(FamilyUpdater $updater, FamilySaver $saver, FamilyRepository $repository, ValidatorInterface $validator)
    {
        $this->updater = $updater;
        $this->saver = $saver;
        $this->repository = $repository;
        $this->validator = $validator;
    }

    private function getFamily(FamilyCode $familyCode): ?FamilyInterface
    {
        return $this->repository->findOneByIdentifier((string) $familyCode);
    }

    public function addAttributeToFamily(AttributeCode $attributeCode, FamilyCode $familyCode)
    {
        $family = $this->getFamily($familyCode);

        if ($family === null) {
            throw new \UnexpectedValueException(sprintf('Family with code "%s" does not exist', (string) $familyCode));
        }

        $familyAttributeCodes = $family->getAttributeCodes();

        array_push($familyAttributeCodes, (string) $attributeCode);

        $data = [
            'attributes' => $familyAttributeCodes,
        ];

        $this->updater->update($family, $data);

        $violations = $this->validator->validate($family);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($family);
    }
}
