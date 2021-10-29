<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Connector\Processor\Denormalization\Processor;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserProcessor extends Processor
{
    private DatagridViewRepositoryInterface $gridViewRepository;
    private FileStorerInterface $fileStorer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        DatagridViewRepositoryInterface $gridViewRepository,
        FileStorerInterface $fileStorer
    ) {
        parent::__construct($repository, $factory, $updater, $validator, $objectDetacher);
        $this->gridViewRepository = $gridViewRepository;
        $this->fileStorer = $fileStorer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item): ?UserInterface
    {
        if ($item['password'] ?? null) {
            $this->skipItemWithMessage($item, 'Passwords cannot be imported via flat files');
        }

        if (isset($item['avatar']['filePath'])) {
            try {
                $file = $this->fileStorer->store(
                    new \SplFileInfo($item['avatar']['filePath']),
                    'catalogStorage'
                );
                $item['avatar']['filePath'] = $file->getKey();
            } catch (InvalidFile $e) {
                throw InvalidPropertyException::validPathExpected('avatar', self::class, $item['avatar']['filePath']);
            }
        }

        $itemIdentifier = $this->getItemIdentifier($this->repository, $item);
        $user = $this->findOrCreateObject($itemIdentifier);

        $itemDefaultProductGridView = $item['default_product_grid_view'] ?? null;
        if (null !== $itemDefaultProductGridView) {
            $defaultProductGridView = $this->gridViewRepository->findPrivateDatagridViewByLabel($itemDefaultProductGridView, $user) ??
                $this->gridViewRepository->findPublicDatagridViewByLabel($itemDefaultProductGridView);
            if (null !== $defaultProductGridView) {
                $item['default_product_grid_view'] = $defaultProductGridView->getId();
            }
        }

        try {
            $this->updater->update($user, $item);
            if (null === $user->getId()) {
                $this->updater->update($user, ['password' => \uniqid('tmp_pwd')]);
            }
        } catch (PropertyException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validate($user);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($user);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($itemIdentifier, $user);
        }

        return $user;
    }
}
