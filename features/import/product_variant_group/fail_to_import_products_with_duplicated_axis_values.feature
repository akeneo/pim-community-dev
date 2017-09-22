@javascript
Feature: Fail to import products with duplicated axis values
  In order to have consistent catalog data
  As a product manager
  I need to prevent importing products with duplicated axis values for a same variant group

  @jira https://akeneo.atlassian.net/browse/PIM-6820
  Scenario: Fail to import products with duplicated axis values
    Given an "apparel" catalog configuration
    And the following CSV file to import:
      """
      sku;groups;color;size
      a_red_tee;tshirts;red;size_M
      another_red_tee;tshirts;red;size_M
      """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then there should be 1 product
    And I should see the text "created 1"
    And I should see the text "skipped 1"
    And I should see the text "variant_group: Group \"[tshirts]\" already contains another product with values \"size: [size_M], color: [red]\": another_red_tee"
    And the invalid data file of "product_import" should contain:
      """
      sku;groups;color;size
      another_red_tee;tshirts;red;size_M
      """
