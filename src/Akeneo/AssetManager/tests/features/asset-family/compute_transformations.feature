Feature: Compute the transformations of assets
  In order to automatically generate the variations of my assets images
  As a user
  I want to be able to compute transformations of assets

  @acceptance-back
  Scenario: Successfully compute transformations
    Given an asset family "packshot" with a transformation
    When the user computes transformations from the asset family "packshot"
    Then a job has been launched to compute transformations
