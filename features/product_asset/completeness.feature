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
    And I am logged in as "Julia"
    And I launched the completeness calculator

  @jira https://akeneo.atlassian.net/browse/PIM-4581
  Scenario: Successfully update the completeness at product save
    Given I am on the "jacket-white" product page
    When I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state   | missing_values                                                | ratio |
      | mobile  | de_DE  | warning | name gallery                                                  | 67%   |
      | mobile  | en_US  | warning | name gallery                                                  | 67%   |
      | tablet  | de_DE  | warning | name description weather_conditions rating side_view gallery  | 40%   |
      | tablet  | en_US  | warning | name description weather_conditions rating side_view gallery  | 40%   |
    When I visit the "Media" group
    And I start to manage assets for "gallery"
    And I check the row "paint"
    And I confirm the asset modification
    And I save the product
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state   | missing_values                                                | ratio |
      | mobile  | de_DE  | warning | name gallery                                                  | 83%   |
      | mobile  | en_US  | warning | name gallery                                                  | 83%   |
      | tablet  | de_DE  | warning | name description weather_conditions rating side_view gallery  | 50%   |
      | tablet  | en_US  | warning | name description weather_conditions rating side_view gallery  | 50%   |
    Given I generate missing variations for asset paint
    And I am on the "jacket-white" product page
    When I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state   | missing_values                                         | ratio |
      | mobile  | de_DE  | success |                                                        | 100%   |
      | mobile  | en_US  | success |                                                        | 100%   |
      | tablet  | de_DE  | warning | name description weather_conditions rating side_view   | 60%   |
      | tablet  | en_US  | warning | name description weather_conditions rating side_view   | 60%   |

