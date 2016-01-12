@deprecated @javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to know which headers are not well formed

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3369
  Scenario: Skip import with a not available locale for channel of a localizable attribute
    Given the following CSV file to import:
      """
      sku;description-fr_FR-print
      SKU-001;"my name"
      """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "The locale \"fr_FR\" of the field \"description-fr_FR-print\" is not available in scope \"print\""
