<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Fixture;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserBuilder
{
    private $factory;
    private $updater;
    private $saver;
    private $validator;

    public function __construct(
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        BulkSaverInterface $saver,
        ValidatorInterface $validator
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public function build(array $usersInStandardFormat): void
    {
        $users = [];

        foreach ($usersInStandardFormat as $userData) {
            $user = $this->factory->create();
            $this->updater->update($user, $userData);

            if (array_key_exists('enabled', $userData) && true === $userData['enabled']) {
                $user->setEnabled(true);
            }

            $this->validate($user);

            $users[] = $user;
        }

        $this->saver->saveAll($users);
    }

    /**
     * @param mixed $object
     *
     * @throws \InvalidArgumentException
     */
    private function validate($object)
    {
        $violations = $this->validator->validate($object);

        if (0 !== $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(sprintf(
                'Object "%s" is not valid, cf following constraint violations "%s"',
                ClassUtils::getClass($object),
                implode(', ', $messages)
            ));
        }
    }
}
