Feature: Create a task
  In order to schedule specific tasks
  As an IT administrator
  I need to be able to create a task definition

  Scenario: Successfully create a new task definition
    When I create a new task definition with the rule_run code
    Then the task definition with rule_run code should exist
    And no exception should have been thrown
