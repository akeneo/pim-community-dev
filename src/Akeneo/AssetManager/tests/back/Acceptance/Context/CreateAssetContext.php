<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class CreateAssetContext implements Context
{
    /** @var CreateAssetHandler */
    private $createAssetHandler;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var AssetFamilyRepositoryInterface  */
    private $assetFamilyRepository;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        CreateAssetHandler $createAssetHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext
    ) {
        $this->createAssetHandler = $createAssetHandler;
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->validator = $validator;
        $this->exceptionContext = $exceptionContext;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @When /^the user creates a asset "([^"]+)" for entity "([^"]+)" with:$/
     */
    public function theUserCreatesAAssetWith(
        string $code,
        string $assetFamilyIdentifier,
        TableNode $updateTable
    ) {
        $updates = current($updateTable->getHash());
        $command = new CreateAssetCommand(
            $assetFamilyIdentifier,
            $code,
            json_decode($updates['labels'], true)
        );

        $this->violationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->createAssetHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is a asset with:$/
     */
    public function thereIsAAssetWith(TableNode $assetFamilyTable)
    {
        $expectedInformation = current($assetFamilyTable->getHash());
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($expectedInformation['entity_identifier']);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $actualAsset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString($expectedInformation['code'])
        );
        $attributeAsLabel = $assetFamily->getAttributeAsLabelReference();
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualAsset,
            $attributeAsLabel
        );
    }

    /**
     * @Then there is no ':assetCode' asset in the ':assetFamilyIdentifier' asset family
     */
    public function thereIsNoAssetForTheAssetFamily(string $assetCode, string $assetFamilyIdentifier): void
    {
        try {
            $this->assetRepository->getByAssetFamilyAndCode(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AssetCode::fromString($assetCode)
            );
        } catch (AssetNotFoundException $e) {
            return;
        }

        throw new \Exception('The asset exists.');
    }

    private function assertSameLabels(array $expectedLabels, Asset $asset, AttributeAsLabelReference $attributeAsLabel)
    {
        $valueCollection = $asset->getValues()->normalize();

        $actualLabels = $this->getLabelsFromValues($valueCollection, $attributeAsLabel->normalize());

        $differences = array_merge(
            array_diff($expectedLabels, $actualLabels),
            array_diff($actualLabels, $actualLabels)
        );

        Assert::isEmpty(
            $differences,
            sprintf('Expected labels "%s", but found %s', json_encode($expectedLabels), json_encode($actualLabels))
        );
    }

    private function getLabelsFromValues(array $valueCollection, string $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $localeCode = $value['locale'];
                    $label = (string) $value['data'];
                    $labels[$localeCode] = $label;
                }

                return $labels;
            },
            []
        );
    }

    /**
     * @Given /^there should be no asset$/
     */
    public function thereShouldBeNoAsset()
    {
        $assetFamilyCount = $this->assetRepository->count();
        Assert::same(
            0,
            $assetFamilyCount,
            sprintf('Expected to have 0 asset family. %d found.', $assetFamilyCount)
        );
    }

    /**
     * @Given /^(\d+) random assets for an asset family$/
     */
    public function randomAssetsForAAssetFamily(int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $command = new CreateAssetCommand(
                'designer',
                uniqid('asset_'),
                []
            );

            $this->violationsContext->addViolations($this->validator->validate($command));

            ($this->createAssetHandler)($command);
        }
    }
}
