Feature: Edit the transformations of an asset family
  In order to automatically enrich image values of an asset
  As a user
  I want to be able to define transformations for an asset family

  @acceptance-back
  Scenario: Add assets transformations to an asset family
    Given an asset family "packshot" with no transformation
    When the user edits the "packshot" family to add a valid transformation
    Then the "packshot" family should have a transformation

  @acceptance-back
  Scenario: Delete asset transformations from an asset family
    Given an asset family "packshot" with a transformation
    When the user edits the "packshot" family to remove every transformation
    Then the "packshot" family should not have any transformation

  @acceptance-back
  Scenario: Do not update transformations when the user does not provide them
    Given an asset family "packshot" with a transformation
    When the user edits the "packshot" family without providing any transformation
    Then the "packshot" family should have a transformation
