# How to add an operation

In the Asset Manager, you can apply a transformation, defined by a set of operations, from a source file to automatically generate a target file.
For example, you can generate a thumbnail in a specific attribute to export it to your e-commerce tool.
Akeneo PIM is delivered with a set of default operations, but you can add yours for your needs. 

In this example, we will present you how to create a new operation and use it in a transformation configuration. 

## Create the operation description

In this example, we will implement a new operation to rotate an image.
This operation only needs an angle as parameter. 
Note than it already exists a liip/imagine filter to do this operation. 

An operation description inherits from `Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation`.
First, create your operation:

```php
<?php // src/Akeneo/AssetManager/back/Domain/Model/AssetFamily/Transformation/Operation/RotateOperation.php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Webmozart\Assert\Assert;

class RotateOperation implements Operation
{
    /** @var float */
    private $angle;

    public function __construct(float $angle)
    {
        $this->angle = $angle;
    }

    public static function getType(): string
    {
        return 'rotate';
    }

    public static function create(array $parameters): Operation
    {
        Assert::keyExists($parameters, 'angle');
        Assert::float($parameters['angle']);

        return new self($parameters['angle']);
    }

    public function normalize(): array
    {
        return [
            'type' => self::getType(),
            'parameters' => [
                'angle' => $this->angle,
            ],
        ];
    }

    public function getAngle(): float
    {
        return $this->angle;
    }
}
```

Then add it to the DI to be able to be constructed through its Factory:

```yaml 
    # src/Akeneo/AssetManager/back/Infrastructure/Symfony/Resources/config/parameters.yml
    asset_manager_transformation_operation:
        [...]
        - 'Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\RotateOperation'
```

## Create the operation applier

The OperationApplier will update a file, by applying this transformation in it.
In our use case, the `RotateOperationApplier` will update a file by rotating it with n degrees.

Declare a new php class inheriting from OperationApplier.
You can do any operation in an applier (call a library, call directly a bash command, etc).
As it already exists a liip/imagine filter rotating an image, we use the built-in filter with the imagine FilterManager.

```php
<?php // src/Akeneo/AssetManager/back/Infrastructure/Transformation/Operation/RotateOperationApplier.php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\RotateOperation;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class RotateOperationApplier implements OperationApplier
{
    /** @var FilterManager */
    private $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    public function supports(Operation $operation): bool
    {
        return $operation instanceof RotateOperation;
    }

    public function apply(File $file, Operation $operation): File
    {
        Assert::isInstanceOf($operation, RotateOperation::class);

        $image = new FileBinary($file->getRealPath(), $file->getMimeType());
        $computedImage = $this->filterManager->applyFilters(
            $image,
            [
                'filters' => [
                    'rotate' => [
                        'angle' => $operation->getAngle(),
                    ],
                ],
                'quality' => 100,
            ]
        );

        file_put_contents($file->getRealPath(), $computedImage->getContent());

        return $file;
    }
}
```

Then, declare with the DI with a tag `akeneo_assetmanager.transformation.operation_applier`, to be applicable.

```yaml
    # src/Akeneo/AssetManager/back/Infrastructure/Symfony/Resources/config/compute_transformations.yml
    Akeneo\AssetManager\Infrastructure\Transformation\Operation\RotateOperationApplier:
        arguments:
            - '@liip_imagine.filter.manager'
        tags:
            - { name: akeneo_assetmanager.transformation.operation_applier }
```

## Use it

You can now use it in your Asset Family configuration, in the transformation section:

```yaml
    [...]
    transformations: [
        {
            source: [a_source]
            target: [a_target]
            operations: [
                {
                    type: rotate,
                    parameters: [
                        angle: 90
                    ]                   
                }
            ]           
        }
    ]
```
