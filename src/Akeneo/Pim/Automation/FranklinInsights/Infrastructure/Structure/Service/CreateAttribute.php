<?php


namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateAttribute implements CreateAttributeInterface
{
    /**
     * @var SimpleFactoryInterface
     */
    private $factory;
    /**
     * @var ObjectUpdaterInterface
     */
    private $updater;
    /**
     * @var SaverInterface
     */
    private $saver;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CreateAttribute constructor.
     * @param AttributeFactory $factory
     * @param AttributeUpdater $updater
     * @param AttributeSaver $saver
     * @param ValidatorInterface $validator
     */
    public function __construct(AttributeFactory $factory, AttributeUpdater $updater, AttributeSaver $saver, ValidatorInterface $validator)
    {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public function create(AttributeCode $attributeCode, AttributeLabel $attributeLabel, string $attributeType, string $attributeGroupCode)
    {
        $data = [
            'code' => (string) $attributeCode,
            'type' => (string) $attributeType,
            'group' => (string) $attributeGroupCode,
            'labels' => [
                'en_US' => (string) $attributeLabel
            ],
            'localizable' => false,
            'scopable' => false,
        ];

        $attribute = $this->factory->create();
        $this->updater->update($attribute, $data);

        $violations = $this->validator->validate($attribute);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($attribute);
    }
}