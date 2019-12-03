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
      | sku              | family   | manufacturer | weather_conditions | color | name-en_US | name-fr_FR  | price          | rating | size | lace_color  |
      | sneakers         | sneakers | Converse     | hot                | blue  | Sneakers   | Espadrilles | 69 EUR, 99 USD | 4      | 43   | laces_white |
      | sandals          | sandals  |              |                    | white |            | Sandales    |                |        |      |             |
      | my_nice_sneakers |          |              |                    |       |            |             |                |        |      |             |
    And the following product values:
      | product  | attribute   | value                 | locale | scope  |
      | sneakers | description | Great sneakers        | en_US  | mobile |
      | sneakers | description | Really great sneakers | en_US  | tablet |
      | sneakers | description | Grandes espadrilles   | fr_FR  | mobile |
      | sandals  | description | Super sandales        | fr_FR  | tablet |
      | sandals  | description | Super sandales        | fr_FR  | mobile |
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully display the completeness of the products
    Given I am on the "sneakers" product page
    When I visit the "Completeness" column tab
    Then I should see the "tablet" completeness in position 1
    And I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | warning | 1              | 88%   |
      | tablet  | fr_FR  | warning | 2              | 77%   |
      | mobile  | en_US  | success | 0              | 100%  |
      | mobile  | fr_FR  | success | 0              | 100%  |
    When I am on the products grid
    Then I am on the "sandals" product page
    And I visit the "Attributes" column tab
    And the Name, Description fields should be highlighted
    And the Manufacturer, SKU fields should not be highlighted
    And the Product information, Marketing, Sizes, Media groups should be highlighted
    And the Colors group should not be highlighted
    And I visit the "Completeness" column tab
    And I visit the "Attributes" column tab
    When I switch the locale to "fr_FR"
    And I visit the "Completeness" column tab
    And I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | warning | 6              | 25%   |
      | tablet  | fr_FR  | warning | 4              | 50%   |
      | mobile  | en_US  | warning | 3              | 40%   |
      | mobile  | fr_FR  | warning | 2              | 60%   |

  @critical
  Scenario: Successfully display the completeness of the products in the grid
    Given I am on the products grid
    When I switch the locale to "en_US"
    And I switch the scope to "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 40%   |
    When I switch the scope to "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 88%   |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 25%   |
    When I switch the locale to "fr_FR"
    And I switch the scope to "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 60%   |
    When I switch the scope to "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 77%   |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 50%   |

  Scenario: Display the completeness badge for the scope and locale
    Given I am on the "sneakers" product page
    When I visit the "Completeness" column tab
    And I switch the scope to "Tablet"
    Then the completeness badge label should show "Complete: 88%"
    When I switch the locale to "fr_FR"
    Then the completeness badge label should show "Complete: 77%"
    When I switch the scope to "Mobile"
    Then the completeness badge label should show "Complete: 100%"
    When I switch the locale to "en_US"
    Then the completeness badge label should show "Complete: 100%"
