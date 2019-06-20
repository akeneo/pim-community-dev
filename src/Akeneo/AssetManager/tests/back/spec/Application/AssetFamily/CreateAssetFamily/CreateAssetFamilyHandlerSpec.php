<?php

namespace spec\Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateAssetFamilyHandlerSpec extends ObjectBehavior
{
    function let(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->beConstructedWith($assetFamilyRepository, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAssetFamilyHandler::class);
    }

    function it_creates_a_new_asset(AssetFamilyRepositoryInterface $assetFamilyRepository) {
        $createAssetFamilyCommand = new CreateAssetFamilyCommand(
            'brand',
            [
                'en_US' => 'Intel',
                'fr_FR' => 'Intel',
            ]
        );

        $assetFamilyRepository->create(Argument::that(function ($assetFamily) {
            return $assetFamily instanceof AssetFamily
                && 'brand' === $assetFamily->getIdentifier()->normalize()
                && 'Intel' === $assetFamily->getLabel('en_US')
                && 'Intel' === $assetFamily->getLabel('fr_FR');
        }))->shouldBeCalled();

        $this->__invoke($createAssetFamilyCommand);
    }
}
