<?php

namespace Context;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Context\AssertionContext as BaseAssertionContext;

/**
 * Assertion context
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseAssertionContext extends BaseAssertionContext
{
    /**
     * @Then /^the asset basket should contain (.*)$/
     */
    public function theAssetBasketShouldContain($entities)
    {
        foreach ($this->getMainContext()->listToArray($entities) as $entity) {
            $this->getAssetBasketItem($entity);
        }
    }

    /**
     * @Then /^the "([^"]*)" asset gallery should contain (.*)$/
     */
    public function theAssetGalleryShouldContains($field, $entities)
    {
        $fieldContainer = $this->getCurrentPage()->findFieldContainer($field);

        $entities = $this->getMainContext()->listToArray($entities);
        foreach ($entities as $entity) {
            $this->getAssetGalleryItem($entity, $fieldContainer);
        }

        if (count($fieldContainer->findAll('css', '.AknAssetCollectionField-listItem')) !== count($entities)) {
            throw $this->createExpectationException(
                sprintf(
                    'Incorrect item count in asset gallery (expected: %s, current: %s',
                    count($entities),
                    count($fieldContainer->findAll('css', '.AknAssetCollectionField li'))
                )
            );
        }
    }

    /**
     * @Then /^the "([^"]*)" asset gallery should be empty$/
     *
     * @throws ExpectationException
     */
    public function theAssetGalleryShouldBeEmpty($field)
    {
        $fieldContainer = $this->getCurrentPage()->findFieldContainer($field);

        if (0 !== count($fieldContainer->findAll('css', '.AknAssetCollectionField-listItem'))) {
            throw $this->createExpectationException(
                sprintf(
                    'Incorrect item count in asset gallery (expected: %s, current: %s)',
                    0,
                    count($fieldContainer->findAll('css', '.AknAssetCollectionField li'))
                )
            );
        }
    }

    /**
     * @param $version
     *
     * @Then /^the version (\d+) should be marked as published$/
     *
     * @throws ExpectationException
     */
    public function versionShouldBeMarkedAsPublished($version)
    {
        $row = $this->spin(function () use ($version) {
            return $this->getCurrentPage()->find('css', '.history-block tr[data-version="' . $version . '"]');
        }, sprintf('Cannot find history row for version "%d"', $version));

        if (!$row->find('css', '.label-published')) {
            throw $this->createExpectationException(
                sprintf('Expecting to see version %d marked as published, but is not', $version)
            );
        }
    }

    /**
     * @Then /^the asset basket item "([^"]+)" should contain the thumbnail for channel "([^"]+)"(?: and locale "([^"]+)")?$/
     *
     * @param string      $code
     * @param string      $channelCode
     * @param string|null $localeCode
     *
     * @throws ExpectationException
     */
    public function theAssetBasketItemShouldContainThumbnailForContext($code, $channelCode, $localeCode = null)
    {
        $baksetItem = $this->getAssetBasketItem($code);
        $thumbnail  = $this->spin(function () use ($baksetItem) {
            return $baksetItem->find('css', '.AknAssetCollectionField-assetThumbnail');
        }, 'Impossible to find the thumbnail');

        $this->checkThumbnailUrlForContext($thumbnail, $code, $channelCode, $localeCode);
    }

    /**
     * @Then /^the "([^"]*)" asset gallery item "([^"]*)" should contain the thumbnail for channel "([^"]+)"(?: and locale "([^"]+)")?$/
     *
     * @param string      $field
     * @param string      $code
     * @param string      $channelCode
     * @param string|null $localeCode
     *
     * @throws ExpectationException
     */
    public function theAssetGalleryItemShouldContainThumbnailForContext($field, $code, $channelCode, $localeCode = null)
    {
        $fieldContainer = $this->getCurrentPage()->findFieldContainer($field);
        $galleryItem    = $this->getAssetGalleryItem($code, $fieldContainer);
        $thumbnail      = $galleryItem->find('css', '.AknAssetCollectionField-assetThumbnail');

        $this->checkThumbnailUrlForContext($thumbnail, $code, $channelCode, $localeCode);
    }

    /**
     * @Then /^I should see that "([^"]+)" characters are remaining$/
     *
     * @param int $expectedNumber
     *
     * @throws ExpectationException
     */
    public function iShouldSeeThatCharactersAreRemaining($expectedNumber)
    {
        $modalBodyContent     = $this->getCurrentPage()->find('css', '.modal-body');
        $remainingCharContent = $modalBodyContent->find('css', '.remaining-chars');
        $remainingChar        = $remainingCharContent->getText();

        if ($remainingChar !== $expectedNumber) {
            throw $this->createExpectationException(
                sprintf('Expecting to see "%s" remaining chars but got "%s".', $expectedNumber, $remainingChar)
            );
        }
    }

    /**
     * @Given /^I should not be able to send the comment$/
     *
     * @throws ExpectationException
     */
    public function iShouldNotBeAbleToSendTheComment()
    {
        $modalFooterContent = $this->getCurrentPage()->find('css', '.modal-footer');
        $disabledOkBtn      = $modalFooterContent->find('css', '.ok.disabled');

        if (null === $disabledOkBtn) {
            throw $this->createExpectationException('Expecting to see the Send button disabled, it was not.');
        }
    }

    /**
     * @Then /^I should see a project validation error "([^"]*)"$/
     *
     * @param string $expectedErrorMessage
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAProjectValidationError($expectedErrorMessage)
    {
        $projectModal = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.control-group');
        }, 'Impossible to find the modal project form');

        $errors = $this->spin(function () use ($projectModal) {
            $errors = $projectModal->findAll('css', '.AknFieldContainer-validationError');

            return count($errors) > 0 ? $errors : false;
        }, 'Impossible to find validation errors');

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getText();
        }

        if (!in_array($expectedErrorMessage, $errorMessages)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see the validation error "%s", but not found.',
                    $expectedErrorMessage
                )
            );
        }
    }

    /**
     * @param string $code
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function getAssetBasketItem($code)
    {
        return $this->spin(function () use ($code) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('.item-picker-basket li[data-itemCode="%s"]', $code));
        }, sprintf('Cannot find asset "%s" in basket', $code));
    }

    /**
     * @param string      $code
     * @param NodeElement $fieldContainer
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function getAssetGalleryItem($code, NodeElement $fieldContainer)
    {
        return $this->spin(function () use ($code, $fieldContainer) {
            return $fieldContainer->find('css', sprintf('.AknAssetCollectionField-listItem[data-asset="%s"]', $code));
        }, sprintf('Cannot find the gallery item "%s"', $code));
    }

    /**
     * @param NodeElement $thumbnail
     * @param string      $code
     * @param string      $channelCode
     * @param string|null $localeCode
     *
     * @throws ExpectationException
     */
    protected function checkThumbnailUrlForContext(NodeElement $thumbnail, $code, $channelCode, $localeCode = null)
    {
        $rawStyle = $thumbnail->getAttribute('style');

        if (!preg_match('`url\(\'(.*)\'\)`', $rawStyle, $matches)) {
            throw $this->createExpectationException(sprintf(
                'Expecting thumbnail of asset "%s" to contain a valid url.',
                $code
            ));
        }
        $thumbnailUrl = $matches[1];

        if (false === strpos($thumbnailUrl, sprintf('/%s/', $channelCode))) {
            throw $this->createExpectationException(sprintf(
                'Expecting thumbnail url of asset "%s" to contain scope "%s", full url is "%s".',
                $code,
                $channelCode,
                $thumbnailUrl
            ));
        }

        if (null !== $localeCode && false === strpos($thumbnailUrl, sprintf('/%s', $localeCode))) {
            throw $this->createExpectationException(sprintf(
                'Expecting thumbnail url of asset "%s" to contain locale code "%s", full url is "%s".',
                $code,
                $localeCode,
                $thumbnailUrl
            ));
        }
    }
}
