@javascript @restore-product-feature-enabled
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  Scenario: Revert a product with simple and multiple reference data values
    Given a "footwear" catalog configuration
    And the following products:
      | sku       | family | heel_color | sole_fabric   |
      | red-heels | heels  | yellow     | neoprene,silk |
    And the following product values:
      | product     | attribute   | value        |
      | red-heels   | heel_color  | blue         |
      | red-heels   | sole_fabric | cashmerewool |
    And I am logged in as "Julia"
    And I am on the "red-heels" product page
    When I visit the "History" column tab
    Then I should see history:
      | version | property    | value         |
      | 2       | Heel color  | blue          |
      | 2       | Sole fabric | cashmerewool  |
    When I revert the product version number 1 and then see 3 total versions
    Then the product "red-heels" should have the following values:
      | heel_color  | [yellow]           |
      | sole_fabric | [neoprene], [silk] |
