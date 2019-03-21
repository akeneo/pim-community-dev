@javascript
Feature: Add products to a group
  In order to manage existing groups for the catalog
  As a product manager
  I need to be able to add products to a group

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code       | label-en_US | type  |
      | CROSS_SELL | Cross Sell  | XSELL |
    And the following products:
      | sku             | family  | categories        | size | color |
      | sandal-white-37 | sandals | winter_collection | 37   | white |
      | sandal-white-38 | sandals | winter_collection | 38   | white |
      | sandal-white-39 | sandals | winter_collection | 39   | white |
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully add products in groups
    Given I am on the "CROSS_SELL" product group page
    And I should see products sandal-white-37 and sandal-white-38
    And I should see the filters family, in_group and enabled
    And I check the row "sandal-white-37"
    And I check the row "sandal-white-38"
    And I save the group
    Then the row "sandal-white-37" should be checked
    And the row "sandal-white-38" should be checked
