@javascript
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
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text " The field \"comment-fr_FR-mobile\" does not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3374
  Scenario: Skip import with a not expected channel for a global attribute
    Given the following CSV file to import:
      """
      sku;comment-mobile
      SKU-001;"my comment"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text " The field \"comment-mobile\" does not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3375
  Scenario: Skip import with a not expected locale for a global attribute
    Given the following CSV file to import:
      """
      sku;comment-fr_FR
      SKU-001;"my comment"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text " The field \"comment-fr_FR\" does not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3372
  Scenario: Skip import with a not available locale for a localizable attribute
    Given the following CSV file to import:
      """
      sku;name-fr_CA
      SKU-001;"my name"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text " The field \"name-fr_CA\" does not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3370
  Scenario: Skip import with a not existing channel for a scopable attribute
    Given the following CSV file to import:
      """
      sku;description-en_US-noexistingchannel
      SKU-001;"my description"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text " The field \"description-en_US-noexistingchannel\" does not exist"


  @jira https://akeneo.atlassian.net/browse/PIM-3312
  Scenario: Stop imports with attributes where channel is wrong (PIM-3312)
    Given the following CSV file to import:
      """
      sku;name-en_US;description-en_US-wrongchannel
      SKU-001;high heels;red high heels
      SKU-002;rangers;black rangers
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text " The field \"description-en_US-wrongchannel\" does not exist"
    And I should see the text "FAILED"
    And there should be 0 product

  @jira https://akeneo.atlassian.net/browse/PIM-3312
  Scenario: Stop imports with attributes where channel is wrong (PIM-3312)
    Given the following CSV file to import:
      """
      sku;price-FCFA
      SKU-001;100
      SKU-002;50
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text " The field \"price-FCFA\" does not exist"
    And I should see the text "FAILED"
    And there should be 0 product

  @jira https://akeneo.atlassian.net/browse/PIM-3312
  Scenario: Stop imports with attributes where local is wrong (PIM-3312)
    Given the following CSV file to import:
      """
      sku;name-en_US;description-wronglocale-ecommerce
      SKU-001;high heels;red high heels
      SKU-002;rangers;black rangers
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "The field \"description-wronglocale-ecommerce\" does not exist"
    And I should see the text "FAILED"
    And there should be 0 product

  @jira https://akeneo.atlassian.net/browse/PIM-3377
  Scenario: Fail when import invalid attribute with nonexistent specific locale
    Given the following attributes:
      | code                      | type             | localizable | available_locales | group |
      | locale_specific_attribute | pim_catalog_text | 1           | en_US             | other |
    Then I am on the Attribute index page
    And I add the "french" locale to the "mobile" channel
    Given the following CSV file to import:
      """
      sku;locale_specific_attribute-fr_FR
      SKU-001;test value
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "The field \"locale_specific_attribute-fr_FR\" does not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3369
  Scenario: Skip import with a not available locale for channel of a localizable attribute
    Given the following CSV file to import:
      """
      sku;description-fr_FR-print
      SKU-001;"my name"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text "The field \"description-fr_FR-print\" does not exist"

  Scenario: Skip import with many not existing fields
    Given the following CSV file to import:
      """
      sku;unknownfield1;unknownfield2
      SKU-001;"data 1";"data 2"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text "The fields \"unknownfield1, unknownfield2\" do not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3369
  Scenario: Skip import with an unset locale on a localizable attribute
    Given the following CSV file to import:
      """
      sku;description
      SKU-001;"my name"
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text "The field \"description\" needs an additional locale and/or a channel information"
