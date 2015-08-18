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

