<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeLoader
{
    /** @var AttributeFactory */
    private $factory;

    /** @var AttributeUpdater */
    private $updater;

    /** @var AttributeSaver */
    private $saver;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        AttributeFactory $factory,
        AttributeUpdater $updater,
        AttributeSaver $saver,
        ValidatorInterface $validator
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public function create(array $data): void
    {
        $data['group'] = $data['group'] ?? AttributeGroup::DEFAULT_GROUP_CODE;

        $attribute = $this->factory->create();
        $this->updater->update($attribute, $data);

        $constraints = $this->validator->validate($attribute);
        Assert::assertCount(0, $constraints, 'The validation from the attribute creation failed.');

        $this->saver->save($attribute);
    }
}
