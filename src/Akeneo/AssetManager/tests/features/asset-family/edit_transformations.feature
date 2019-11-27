Feature: Edit the transformations of an asset family
  In order to automatically enrich image values of an asset
  As a user
  I want to be able to define transformations for an asset family

  @acceptance-back
  Scenario: Add assets transformations to an asset family
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a valid transformation
    Then the "packshot" family should have 1 transformation

  @acceptance-back
  Scenario: Delete asset transformations from an asset family
    Given an asset family "packshot" with a transformation
    When the user edits the "packshot" family to remove every transformation
    Then the "packshot" family should not have any transformation

  @acceptance-back
  Scenario: Do not update transformations when the user does not provide them
    Given an asset family "packshot" with a transformation
    When the user edits the "packshot" family without providing any transformation
    Then the "packshot" family should have 1 transformation

  @acceptance-back
  Scenario: Add assets transformations to an asset family
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add valid complex transformations
    Then the "packshot" family should have the complex transformations

  @acceptance-back @error
  Scenario: Can not update transformations when the source does not exist in family
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a transformation with unknown source
    Then there should be a validation error stating that an attribute is not found

  @acceptance-back @error
  Scenario: Can not update transformations when the target does not exist in family
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a transformation with unknown target
    Then there should be a validation error stating that an attribute is not found

  @acceptance-back @error
  Scenario: Can not update transformations when the source is equal to the target
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a transformation with source equal to a target
    Then there should be a validation error stating that the source is equal to the target

  @acceptance-back @error
  Scenario: Can not update transformations when an operation is set twice in a a transformation
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a transformation with duplicate operations
    Then there should be a validation error stating that an operation is set twice

  @acceptance-back @error
  Scenario: Can not update transformations when limit is reached
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add too much transformations
    Then there should be a validation error stating that the transformation limit is reached

  @acceptance-back @error
  Scenario: Can not update transformations when operation is unknown
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a transformation with unknown operation
    Then there should be a validation error stating that an operation is unknown

  @acceptance-back @error
  Scenario: Can not update transformations when operation provided with bad parameters
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a transformation with wrong parameters for operation
    Then there should be a validation error stating that operation is not instanciable
