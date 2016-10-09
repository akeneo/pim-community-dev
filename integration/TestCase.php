<?php

namespace EnterpriseIntegration;

use Integration\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->extraDirectories[] = __DIR__ . '/' . $this->catalogPath;

        parent::setUp();
    }
}
