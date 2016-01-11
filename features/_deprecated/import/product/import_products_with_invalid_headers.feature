@deprecated @javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to know which headers are not well formed

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3376
  Scenario: Skip import with a not expected locale and channel provided for a global attribute
    Given the following CSV file to import:
      """
      sku;comment-fr_FR-mobile
      SKU-001;"my comment"
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "The field \"comment-fr_FR-mobile\" is not well-formatted, attribute \"comment\" expects no locale, no scope, no currency"

  @jira https://akeneo.atlassian.net/browse/PIM-3374
  Scenario: Skip import with a not expected channel for a global attribute
    Given the following CSV file to import:
      """
      sku;comment-mobile
      SKU-001;"my comment"
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "The field \"comment-mobile\" is not well-formatted, attribute \"comment\" expects no locale, no scope, no currency"

  @jira https://akeneo.atlassian.net/browse/PIM-3375
  Scenario: Skip import with a not expected locale for a global attribute
    Given the following CSV file to import:
      """
      sku;comment-fr_FR
      SKU-001;"my comment"
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "The field \"comment-fr_FR\" is not well-formatted, attribute \"comment\" expects no locale, no scope, no currency"

  @jira https://akeneo.atlassian.net/browse/PIM-3372
  Scenario: Skip import with a not available locale for a localizable attribute
    Given the following CSV file to import:
      """
      sku;name-fr_CA
      SKU-001;"my name"
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "Locale fr_CA does not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3370
  Scenario: Skip import with a not existing channel for a scopable attribute
    Given the following CSV file to import:
      """
      sku;description-en_US-noexistingchannel
      SKU-001;"my description"
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "Channel noexistingchannel does not exist"

