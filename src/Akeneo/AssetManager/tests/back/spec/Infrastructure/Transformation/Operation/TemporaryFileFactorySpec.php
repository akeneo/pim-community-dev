<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\TemporaryFileFactory;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class TemporaryFileFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->shouldHaveType(TemporaryFileFactory::class);
    }

    function it_creates_a_temporary_file()
    {
        $result = $this->createFromContent('fileContent');
        $result->shouldBeAnInstanceOf(File::class);
        $result->getPath()->shouldEqual(sys_get_temp_dir());
        $result->getFilename()->shouldStartWith('akeneo_asset_manager_');
        Assert::eq(file_get_contents($result->getPathname()->getWrappedObject()), ('fileContent'));
    }
}
