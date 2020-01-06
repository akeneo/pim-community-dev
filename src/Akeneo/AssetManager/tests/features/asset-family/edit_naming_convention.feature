Feature: Edit the naming convention of an asset family
  In order to automatically enrich asset properties
  As a user
  I want to be able to define naming convention for an asset family

  @acceptance-back
  Scenario: Set naming convention to an asset family
    Given an asset family
    When the user edits the family to set a valid naming convention
    Then the family naming convention should be set

  @acceptance-back
  Scenario: Do not update naming convention if it is not set
    Given an asset family with a naming convention
    When the user edits the family without naming convention
    Then the family naming convention should be set

  @acceptance-back
  Scenario: Cannot update naming convention if the property is invalid
    Given an asset family with a naming convention
    When the user edits the family naming convention with an invalid property
    Then there should be a validation error stating that the property is invalid

  @acceptance-back
  Scenario: Cannot update naming convention if the source is missing
    Given an asset family with a naming convention
    When the user edits the family naming convention with an empty source
    Then there should be a validation error stating that the source must be defined

  @acceptance-back
  Scenario: Cannot update naming convention if the source is not localizable
    Given an asset family with a naming convention
    When the user edits the family naming convention with a localizable source
    Then there should be a validation error stating that the source must not be localizable

  @acceptance-back
  Scenario: Cannot update naming convention if the pattern is missing
    Given an asset family with a naming convention
    When the user edits the family naming convention without pattern
    Then there should be a validation error stating that the pattern must be defined

  @acceptance-back
  Scenario: Cannot update naming convention if the pattern is not a valid regex
    Given an asset family with a naming convention
    When the user edits the family naming convention with invalid pattern
    Then there should be a validation error stating that the pattern is not valid

  @acceptance-back
  Scenario: Cannot update naming convention if the strict is missing
    Given an asset family with a naming convention
    When the user edits the family naming convention without strict
    Then there should be a validation error stating that the strict must be defined
