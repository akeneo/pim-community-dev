Feature: Security
  In order to check security of the external command
  As a developer
  I need to be able to execute shell commands without security issues

  @jira https://akeneo.atlassian.net/browse/PIM-6062
  Scenario: Fail when trying to inject command
    Given file "%tmp%/this_will_raise_an_error.txt" should not exist
    When I run '$(touch %tmp%/this_will_raise_an_error.txt)' in background
    # This wait is exceptional, as otherwise we have an Exception if launched in foreground.
    And I wait 3 seconds
    Then file "%tmp%/this_will_raise_an_error.txt" should not exist
