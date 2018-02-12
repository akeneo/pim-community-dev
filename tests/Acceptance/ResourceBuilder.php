<?php


namespace Akeneo\Test\Acceptance;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResourceBuilder
{
    /**
     * @var SimpleFactoryInterface
     */
    private $resourceFactory;
    /**
     * @var ObjectUpdaterInterface
     */
    private $resourceUpdater;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param SimpleFactoryInterface $resourceFactory
     * @param ObjectUpdaterInterface $resourceUpdate
     * @param ValidatorInterface     $validator
     */
    public function __construct(
        SimpleFactoryInterface $resourceFactory,
        ObjectUpdaterInterface $resourceUpdate,
        ValidatorInterface $validator
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceUpdater = $resourceUpdate;
        $this->validator = $validator;
    }

    /**
     * @param array $data
     *
     * @return object
     *
     * @throws \Exception
     */
    public function build(array $data)
    {
        $category = $this->resourceFactory->create();
        $this->resourceUpdater->update($category, $data);
        $errors = $this->validator->validate($category);

        if (0 !== $errors->count()) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = sprintf(
                    "\n- property path: %s\n- message: %s",
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }

            throw new \Exception("An error occurred on resource creation:" . implode("\n", $errorMessages));
        }

        return $category;
    }
}
