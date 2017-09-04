@javascript @skip
Feature: Remove products from a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to remove products from a variant group

  Background:
    Given the "footwear" catalog configuration
    And the following variant groups:
      | code   | label-en_US | axis       | type    |
      | SANDAL | Sandal      | size,color | VARIANT |
    And the following products:
      | sku             | groups | family  | categories        | size | color |
      | sandal-white-37 | SANDAL | sandals | winter_collection | 37   | white |
      | sandal-white-38 | SANDAL | sandals | winter_collection | 38   | white |
      | sandal-white-39 | SANDAL | sandals | winter_collection | 39   | white |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3736
  Scenario: Successfully remove a product from the variant group
    Given I am on the "SANDAL" variant group page
    Then the grid should contain 3 elements
    When I uncheck the row "sandal-white-37"
    And I press the "Save" button
    Then I should see the text "Products: 2"
    And the row "sandal-white-37" should not be checked
