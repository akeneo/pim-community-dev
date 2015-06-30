@deprecated @javascript
Feature: Fail to import products with missing axes
  In order to have consistent catalog data
  As a product manager
  I need to prevent importing products with a variant group that have missing axes

  Scenario: Fail to import products with a variant group that have missing axes
    Given an "apparel" catalog configuration
    And the following CSV file to import:
      """
      sku;groups;color;size;chest_size;waist_size
      red_sweat;sweaters;;size_XL;;
      red_tee;tshirts;red;size_M;;
      red_jacket;jackets;;;;waist_size_L
      """
    And the following job "product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    When I am on the "product_import" import job page
    And I launch the import job
    And I wait for the "product_import" job to finish
    Then I should see:
    """
    The product "red_sweat" is in the variant group "sweaters" but it misses the following axes: color.
    """
    And I should see:
    """
    The product "red_jacket" is in the variant group "jackets" but it misses the following axes: chest_size, color.
    """
    And there should be 1 product
