<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Job;

use Akeneo\Bundle\BatchBundle\Job\ExitStatus;

/**
 * Tests related to the ExitStatus class
 *
 */
class ExitStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testSetExitCode()
    {
        $status = new ExitStatus(ExitStatus::COMPLETED);
        $status->setExitCode(ExitStatus::STOPPED);
        $this->assertEquals(ExitStatus::STOPPED, $status->getExitCode());
    }

    public function testSetExitCodeUnknown()
    {
        $status = new ExitStatus(ExitStatus::COMPLETED);
        $status->setExitCode(10);
        $this->assertEquals(ExitStatus::UNKNOWN, $status->getExitCode());
    }

    public function testExitStatusNullDescription()
    {
        $status = new ExitStatus("10", null);
        $this->assertEquals("", $status->getExitDescription());
    }

    public function testExitStatusBooleanInt()
    {
        $status = new ExitStatus("10");
        $this->assertEquals("10", $status->getExitCode());
    }

    public function testExitStatusConstantsContinuable()
    {
        $status = new ExitStatus(ExitStatus::EXECUTING);
        $this->assertEquals("EXECUTING", $status->getExitCode());
    }

    public function testExitStatusConstantsFinished()
    {
        $status = new ExitStatus(ExitStatus::COMPLETED);
        $this->assertEquals("COMPLETED", $status->getExitCode());
    }

    /**
     * Test equality of exit statuses.
     *
     * @throws Exception
     */
    public function testEqualsWithSameProperties()
    {
        $executing = new ExitStatus(ExitStatus::EXECUTING);
        $this->assertEquals($executing, new ExitStatus("EXECUTING"));
    }

    public function testEqualsSelf()
    {
        $status = new ExitStatus("test");
        $this->assertEquals($status, $status);
    }

    public function testEquals()
    {
        $this->assertEquals(new ExitStatus("test"), new ExitStatus("test"));
    }

    /**
     * Test equality of exit statuses.
     *
     * @throws Exception
     */
    public function testEqualsWithNull()
    {
        $executing = new ExitStatus(ExitStatus::EXECUTING);
        $this->assertFalse($executing == null);
    }

    public function testAndExitStatusStillExecutable()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);
        $executing3 = new ExitStatus(ExitStatus::EXECUTING);

        $this->assertEquals(
            $executing1->getExitCode(),
            $executing2->logicalAnd($executing3)->getExitCode()
        );
    }

    public function testAndExitStatusWhenFinishedAddedToContinuable()
    {
        $completed1 = new ExitStatus(ExitStatus::COMPLETED);
        $executing = new ExitStatus(ExitStatus::EXECUTING);
        $completed2 = new ExitStatus(ExitStatus::COMPLETED);

        $this->assertEquals(
            $completed1->getExitCode(),
            $executing->logicalAnd($completed2)->getExitCode()
        );
    }

    public function testAndExitStatusWhenContinuableAddedToFinished()
    {
        $completed1 = new ExitStatus(ExitStatus::COMPLETED);
        $executing = new ExitStatus(ExitStatus::EXECUTING);
        $completed2 = new ExitStatus(ExitStatus::COMPLETED);

        $this->assertEquals(
            $completed1->getExitCode(),
            $completed2->logicalAnd($executing)->getExitCode()
        );
    }

    public function testAndExitStatusWhenCustomContinuableAddedToContinuable()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);

        $this->assertEquals(
            "CUSTOM",
            $executing1->logicalAnd(
                $executing2->setExitCode("CUSTOM")
            )->getExitCode()
        );
    }

    public function testAndExitStatusWhenCustomCompletedAddedToCompleted()
    {
        $completed = new ExitStatus(ExitStatus::COMPLETED);
        $executing = new ExitStatus(ExitStatus::EXECUTING);

        $this->assertEquals(
            "COMPLETED_CUSTOM",
            $completed->logicalAnd(
                $executing->setExitCode("COMPLETED_CUSTOM")
            )->getExitCode()
        );
    }

    public function testAndExitStatusFailedPlusFinished()
    {
        $completed1 = new ExitStatus(ExitStatus::COMPLETED);
        $failed1 = new ExitStatus(ExitStatus::FAILED);

        $completed2 = new ExitStatus(ExitStatus::COMPLETED);
        $failed2 = new ExitStatus(ExitStatus::FAILED);

        $this->assertEquals("FAILED", $completed1->logicalAnd($failed1)->getExitCode());
        $this->assertEquals("FAILED", $failed2->logicalAnd($completed2)->getExitCode());
    }

    public function testAndExitStatusWhenCustomContinuableAddedToFinished()
    {
        $completed = new ExitStatus(ExitStatus::COMPLETED);
        $executing = new ExitStatus(ExitStatus::EXECUTING);

        $this->assertEquals(
            "CUSTOM",
            $completed->logicalAnd($executing->setExitCode("CUSTOM"))->getExitCode()
        );
    }

    public function testAddExitCode()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);

        $status = $executing1->setExitCode("FOO");

        $this->assertTrue($executing2 !== $status);
        $this->assertEquals("FOO", $status->getExitCode());
    }

    public function testAddExitCodeToExistingStatus()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);

        $status = $executing1->setExitCode("FOO")->setExitCode("BAR");

        $this->assertTrue($executing2 !== $status);
        $this->assertEquals("BAR", $status->getExitCode());
    }

    public function testAddExitCodeToSameStatus()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);
        $executing3 = new ExitStatus(ExitStatus::EXECUTING);

        $status = $executing1->setExitCode($executing2->getExitCode());
        $this->assertTrue($executing3 !== $status);
        $this->assertEquals($executing3->getExitCode(), $status->getExitCode());
    }

    public function testAddExitDescription()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);

        $status = $executing1->addExitDescription("Foo");

        $this->assertTrue($executing2 !== $status);
        $this->assertEquals("Foo", $status->getExitDescription());
    }

    public function testAddExitDescriptionWithStacktrace()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);

        $status = $executing1->addExitDescription(new \Exception("Foo"));
        $this->assertTrue($executing2 !== $status);
        $description = $status->getExitDescription();
        $this->assertTrue(
            (strstr($description, "Foo") !== -1),
            "Wrong description: ".$description
        );
        $this->assertTrue(
            (strstr($description, "Exception") !== -1),
            "Wrong description: ".$description
        );
    }

    public function testAddExitDescriptionToSameStatus()
    {
        $executing1 = new ExitStatus(ExitStatus::EXECUTING);
        $executing2 = new ExitStatus(ExitStatus::EXECUTING);

        $status = $executing1->addExitDescription("Foo")->addExitDescription("Foo");
        $this->assertTrue($executing2 !== $status);
        $this->assertEquals("Foo", $status->getExitDescription());
    }

    public function testAddEmptyExitDescription()
    {
        $executing = new ExitStatus(ExitStatus::EXECUTING);

        $status = $executing->addExitDescription("Foo")->addExitDescription(null);
        $this->assertEquals("Foo", $status->getExitDescription());
    }

    public function testAddExitCodeWithDescription()
    {
        $bar = new ExitStatus("BAR", "Bar");
        $status = $bar->setExitCode('FOO');

        $this->assertEquals("FOO", $status->getExitCode());
        $this->assertEquals("Bar", $status->getExitDescription());
    }

    public function testAddExitDescriptionToExistingDescription()
    {
        $status = new ExitStatus(ExitStatus::EXECUTING);

        $status->addExitDescription("Foo");
        $status->addExitDescription("Bar");

        $this->assertEquals("Foo;Bar", $status->getExitDescription());
    }

    public function testUnkownIsRunning()
    {
        $unknown = new ExitStatus(ExitStatus::UNKNOWN);
        $this->assertTrue($unknown->isRunning());
    }

    public function testToString()
    {
        $status = new ExitStatus(ExitStatus::COMPLETED, "My test description for completed status");

        $this->assertEquals('[COMPLETED] My test description for completed status', (string) $status);
    }
}
