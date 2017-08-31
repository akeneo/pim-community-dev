@javascript
Feature: Disabled mass edit status of product when user is not owner
  In order to apply product status edit permission
  As a redactor
  I should not be able to mass edit status of product I don't own

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        | name-en_US    | price          | size | color | enabled |
      | boots    | boots    | winter_boots      | Amazing boots | 20 EUR, 25 USD | 40   | black | no      |
      | sneakers | sneakers | winter_boots      | Sneakers      | 50 EUR, 60 USD | 42   | white | no      |
      | sandals  | sandals  | winter_boots      | Sandals       | 5 EUR, 5 USD   | 40   | red   | no      |
      | pump     |          | winter_collection | Pump          | 15 EUR, 20 USD | 41   | blue  | no      |
    And I am logged in as "Mary"

  Scenario: Impossible to mass edit status if not owner of products
    And I am on the products grid
    When I select rows boots, sneakers and pump
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Change status (enable / disable)" operation
    And I enable the products
    And I wait for the "update_product_value" job to finish
    Then product "boots" should be disabled
    And product "sneakers" should be disabled
    But product "pump" should be enabled
