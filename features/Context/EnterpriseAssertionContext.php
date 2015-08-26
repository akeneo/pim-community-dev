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

        if (count($fieldContainer->findAll('css', '.asset-gallery li')) !== count($entities)) {
            throw $this->createExpectationException(
                sprintf(
                    'Incorrect item count in asset gallery (expected: %s, current: %s',
                    count($entities),
                    count($fieldContainer->findAll('css', '.asset-gallery li'))
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
        });

        if (!$row) {
            throw $this->createExpectationException(
                sprintf('Expecting to see history row for version %s, not found', $version)
            );
        }
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
        $thumbnail  = $baksetItem->find('css', '.asset-thumbnail');

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
        $thumbnail      = $galleryItem->find('css', '.asset-thumbnail');

        $this->checkThumbnailUrlForContext($thumbnail, $code, $channelCode, $localeCode);
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
                ->find('css', sprintf('.asset-basket li[data-asset="%s"]', $code));
        });
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
            return $fieldContainer->find('css', sprintf('.asset-gallery li[data-asset="%s"]', $code));
        });
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
