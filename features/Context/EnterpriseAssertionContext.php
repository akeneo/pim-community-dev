<?php

namespace Context;

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
            $entity = $this->spin(function () use ($entity) {
                return $this->getSession()->getPage()
                    ->find('css', sprintf('.asset-basket li[data-asset="%s"]', $entity));
            });
        }
    }

    /**
     * @Then /^the "([^"]*)" asset gallery should contains (.*)$/
     */
    public function theAssetGalleryShouldContains($field, $entities)
    {
        $fieldContainer = $this->getCurrentPage()->findFieldContainer($field);

        $entities = $this->getMainContext()->listToArray($entities);
        foreach ($entities as $entity) {
            $entity = $this->spin(function () use ($entity, $fieldContainer) {
                return $fieldContainer->find('css', sprintf('.asset-gallery li[data-asset="%s"]', $entity));
            });
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
}
