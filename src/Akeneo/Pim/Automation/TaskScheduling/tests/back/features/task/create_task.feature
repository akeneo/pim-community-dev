Feature: Create a task
  In order to schedule specific tasks
  As an IT administrator
  I need to be able to create a task

  Scenario: Successfully create a new task
    When I create a new task
    Then the task with code code should exist
    And no exception should have been thrown
