@javascript
Feature: Fail to import products with multiple variant groups
  In order to have consistent catalog data
  As a product manager
  I need to prevent importing products with multiple variant groups

  Scenario: Fail to import products with multiple variant groups
    Given an "apparel" catalog configuration
    And the following CSV file to import:
      """
      sku;groups;color;size;chest_size;waist_size
      first;tshirts, sweaters, jackets;black;size_S;chest_size_S;waist_size_S
      second;tshirts, sweaters;black;size_S;chest_size_S;waist_size_S
      third;tshirts;black;size_S;chest_size_S;waist_size_S
      """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see "Failed"
    And I should see:
    """
    The product cannot belong to many variant groups: tshirts, sweaters, jackets
    """
    And there should be 0 products
