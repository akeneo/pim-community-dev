@javascript
Feature: Remove products from a group
  In order to manage existing groups for the catalog
  As a product manager
  I need to be able to remove products from a group

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code       | label-en_US | type  |
      | CROSS_SELL | Cross Sell  | XSELL |
    And the following products:
      | sku             | groups     | family  | categories        | size | color |
      | sandal-white-37 | CROSS_SELL | sandals | winter_collection | 37   | white |
      | sandal-white-38 | CROSS_SELL | sandals | winter_collection | 38   | white |
      | sandal-white-39 | CROSS_SELL | sandals | winter_collection | 39   | white |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3736
  Scenario: Successfully remove a product from the group
    Given I am on the "CROSS_SELL" product group page
    Then the grid should contain 3 elements
    When I uncheck the row "sandal-white-37"
    And I press the "Save" button
    Then I should see the text "Products: 2"
    And the row "sandal-white-37" should not be checked
