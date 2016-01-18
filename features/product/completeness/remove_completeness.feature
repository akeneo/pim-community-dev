@javascript
Feature: Display the completeness of a product
  In order to see the completeness of a product in the catalog
  As a product manager
  I need to be able to display the completeness of a product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And the following products:
      | sku      | family   | manufacturer | weather_conditions | color | name-en_US | name-fr_FR  | price          | rating | size | lace_color  |
      | sneakers | sneakers | Converse     | hot                | blue  | Sneakers   | Espadrilles | 69 EUR, 99 USD | 4      | 43   | laces_white |
      | sandals  | sandals  |              |                    | white |            | Sandales    |                |        |      |             |
    And the following product values:
      | product  | attribute   | value                 | locale | scope  |
      | sneakers | description | Great sneakers        | en_US  | mobile |
      | sneakers | description | Really great sneakers | en_US  | tablet |
      | sneakers | description | Grandes espadrilles   | fr_FR  | mobile |
      | sandals  | description | Super sandales        | fr_FR  | tablet |
      | sandals  | description | Super sandales        | fr_FR  | mobile |
    And I am logged in as "Julia"
    And I launched the completeness calculator

  Scenario: Remove completeness from grid when family requirements changed
    Given I am on the "sneakers" family page
    And I visit the "Attributes" tab
    And I switch the attribute "Rating" requirement in channel "Mobile"
    And I save the family
    And I am on the products page
    And I switch the locale to "English (United States)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | -     |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 40%   |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | -     |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 25%   |
    And I switch the locale to "French (France)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | -     |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 60%   |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | -     |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 50%   |

  Scenario: Remove completeness when locales of a channel are deleted
    Given I am on the "tablet" channel page
    And I change the "Locales" to "French (France)"
    And I press the "Save" button
    And I am on the "sneakers" product page
    When I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state    | missing_values        | ratio |
      | mobile  | en_US  | success  |                       | 100%  |
      | mobile  | fr_FR  | success  |                       | 100%  |
      | tablet  | fr_FR  | warning  | description side_view | 78%   |
    When I am on the "sandals" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state    | missing_values              | ratio |
      | mobile  | en_US  | warning  | name price size             | 40%   |
      | mobile  | fr_FR  | warning  | price size                  | 60%   |
      | tablet  | fr_FR  | warning  | price rating side_view size | 50%   |

  Scenario: Remove completeness from grid when locales of a channel are deleted
    Given I am on the "tablet" channel page
    And I change the "Locales" to "French (France)"
    And I press the "Save" button
    And I am on the products page
    And I switch the locale to "English (United States)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 40%   |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | -     |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | -     |
    And I switch the locale to "French (France)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 60%   |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 78%   |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 50%   |
