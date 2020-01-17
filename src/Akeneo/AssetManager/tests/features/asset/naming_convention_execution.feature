Feature: Execute naming convention on an asset
  In order to update automatically values of an asset from a source value
  As a user
  I am able to see the new asset values after the naming convention execution

  @acceptance-back
  Scenario: New asset values are filled automatically from a media file value
    Given an asset family with some attributes and a naming convention with media file source
    When an asset is created with valid values for naming convention execution
    Then there is no exception thrown
    And there is no violations errors
    And the asset should contain the new values based on media file

  @acceptance-back
  Scenario: New asset values are filled automatically from code
    Given an asset family with some attributes and a naming convention with code source
    When an asset is created with valid code for naming convention execution
    Then there is no exception thrown
    And there is no violations errors
    And the asset should contain the new values based on code

  @acceptance-back
  Scenario: Exception is thrown when naming convention source is unknown
    Given an asset family with some attributes and a naming convention with unknown source
    When an asset is created with valid values for naming convention execution
    Then an exception is thrown with message "Could not find attribute for asset family "packshot" and attribute code "unknown""
    And there is no violations errors
    And the asset should be unchanged

  @acceptance-back
  Scenario: Asset is unchanged if naming convention source is localizable and not in strict mode
    Given an asset family with some attributes and a naming convention with localizable target and non strict mode
    When an asset is created with valid values for naming convention execution
    Then there should be a validation error with message 'A locale is expected for attribute "localizabletitle" because it has a value per locale.'
    And there is no exception thrown
    And the asset should be unchanged

  @acceptance-back
  Scenario: Asset is unchanged if naming convention pattern does not match and not in strict mode
    Given an asset family with some attributes and a naming convention with unmatched pattern and non strict mode
    When an asset is created with valid values for naming convention execution
    Then there is no exception thrown
    And there is no violations errors
    And the asset should be unchanged

  @acceptance-back
  Scenario: Exception is thrown when naming convention pattern does not match and in strict mode
    Given an asset family with some attributes and a naming convention with unmatched pattern and strict mode
    When an asset is created with valid values for naming convention execution
    Then an exception is thrown with message "Naming convention pattern does not match"
    And there is no violations errors
    And the asset should be unchanged
