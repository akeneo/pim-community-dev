<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyLoader
{
    /** @var FamilyFactory */
    private $factory;

    /** @var FamilyUpdater */
    private $updater;

    /** @var FamilySaver */
    private $saver;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        FamilyFactory $factory,
        FamilyUpdater $updater,
        FamilySaver $saver,
        ValidatorInterface $validator
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public function create(array $data): void
    {
        $family = $this->factory->create();

        $this->updater->update($family, $data);

        $constraints = $this->validator->validate($family);
        Assert::assertCount(0, $constraints, 'The validation from the family creation failed.');

        $this->saver->save($family);
    }
}
