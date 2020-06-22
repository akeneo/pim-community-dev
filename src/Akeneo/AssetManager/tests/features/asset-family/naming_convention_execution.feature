Feature: Execute naming convention on an asset family
  In order to update automatically values of an asset from a source value
  As a user
  I am able to execute the naming convention on demand

  @acceptance-back
  Scenario: Exisiting assets are updated when re-executing the naming convention
    Given an asset family with some attributes and a naming convention with media file source
    And an asset with valid values for naming convention
    When the naming convention is updated
    And I request the naming convention execution
    Then the asset should contain the updated values based on media file
    And there is no exception thrown

  @acceptance-back
  Scenario: Exception is thrown when the asset does not exists
    Given an asset family with some attributes and a naming convention with media file source
    When I request the naming convention execution on a missing asset
    Then an exception is thrown with message "The asset was not found"

  @acceptance-back
  Scenario: Exception is thrown when the naming convention is invalid
    Given an asset family with some attributes and a naming convention with media file source
    And an asset with valid values for naming convention
    When the naming convention is updated with an invalid configuration
    And I request the naming convention execution
    Then an exception is thrown with message "Attribute "none" does not exist for this asset family"
