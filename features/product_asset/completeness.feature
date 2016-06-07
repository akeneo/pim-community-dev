@javascript
Feature: Display the completeness of a product with assets
  In order to see the completeness of a product in the catalog
  As a product manager
  I need to be able to display the completeness of a product with assets

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku          | family  | categories                 | price          | size | main_color | manufacturer |
      | jacket-white | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | white      | Volcom       |
    And the following product values:
      | product      | attribute   | value                            | locale | scope  |
      | jacket-white | name        | White jacket                     | en_US  |        |
      | jacket-white | name        | Jacket blanc                     | fr_FR  |        |
      | jacket-white | name        | Weißes Jacket                    | de_DE  |        |
      | jacket-white | description | A stylish white jacket           | en_US  | mobile |
      | jacket-white | description | Un Jacket blanc élégant          | fr_FR  | mobile |
      | jacket-white | description | Ein elegantes weißes Jacket      | de_DE  | mobile |
      | jacket-white | description | A really stylish white jacket    | en_US  | tablet |
      | jacket-white | description | Ein sehr elegantes weißes Jacket | de_DE  | tablet |
      | jacket-white | description | Un Jacket blanc élégant          | fr_FR  | tablet |
    And I am logged in as "Julia"
    And I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akeneo.jpg
    And I save the asset
    Then I should not see the text "There are unsaved changes."
    When I am on the "chicagoskyline" asset page
    And I visit the "Variations" tab
    And I upload the reference file akeneo.jpg
    And I save the asset
    Then I should not see the text "There are unsaved changes."
    And I launched the completeness calculator

  Scenario: Successfully update the completeness for a product with non localized asset
    Given I am on the "jacket-white" product page
    And I visit the "Media" group
    And I attach file "akeneo.jpg" to "Side view"
    And I save the product
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | missing_values                      | ratio |
      | mobile  | en_US  | warning | gallery                             | 83%   |
      | tablet  | en_US  | warning | Weather conditions, Rating, gallery | 70%   |
      | mobile  | de_DE  | warning | gallery                             | 83%   |
      | tablet  | de_DE  | warning | Weather conditions, Rating, gallery | 70%   |
    When I visit the "Media" group
    And I start to manage assets for "gallery"
    And I check the row "paint"
    And I confirm the asset modification
    And I save the product
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | missing_values             | ratio |
      | mobile  | en_US  | success |                            | 100%  |
      | tablet  | en_US  | warning | Weather conditions, Rating | 80%   |
      | mobile  | de_DE  | success |                            | 100%  |
      | tablet  | de_DE  | warning | Weather conditions, Rating | 80%   |
    Given I delete the paint variation for channel mobile and locale ""
    When I am on the "jacket-white" product page
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | missing_values             | ratio |
      | mobile  | en_US  | warning | gallery                    | 83%   |
      | tablet  | en_US  | warning | Weather conditions, Rating | 80%   |
      | mobile  | de_DE  | warning | gallery                    | 83%   |
      | tablet  | de_DE  | warning | Weather conditions, Rating | 80%   |

  Scenario: Successfully update the completeness for a product with localized asset
    Given I am on the "jacket-white" product page
    And I visit the "Media" group
    And I attach file "akeneo.jpg" to "Side view"
    And I save the product
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | missing_values                      | ratio |
      | mobile  | en_US  | warning | gallery                             | 83%   |
      | tablet  | en_US  | warning | Weather conditions, Rating, gallery | 70%   |
      | mobile  | de_DE  | warning | gallery                             | 83%   |
      | tablet  | de_DE  | warning | Weather conditions, Rating, gallery | 70%   |
    When I visit the "Media" group
    And I start to manage assets for "gallery"
    And I check the row "paint"
    And I check the row "chicagoskyline"
    And I confirm the asset modification
    And I save the product
    Then I should see the completeness:
      | channel | locale | state   | missing_values             | ratio |
      | mobile  | en_US  | success |                            | 100%  |
      | tablet  | en_US  | warning | Weather conditions, Rating | 80%   |
      | mobile  | de_DE  | success |                            | 100%  |
      | tablet  | de_DE  | warning | Weather conditions, Rating | 80%   |
    And I delete the paint variation for channel mobile and locale ""
    When I am on the "jacket-white" product page
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | missing_values             | ratio |
      | mobile  | en_US  | warning | gallery                    | 83%   |
      | tablet  | en_US  | warning | Weather conditions, Rating | 80%   |
      | mobile  | de_DE  | success |                            | 100%  |
      | tablet  | de_DE  | warning | Weather conditions, Rating | 80%   |
