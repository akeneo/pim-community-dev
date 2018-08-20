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

  Scenario: Successfully update the completeness at product save
    Given I am on the "sneakers" product page
    When I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | warning | 1              | 88%   |
      | tablet  | fr_FR  | warning | 2              | 77%   |
      | mobile  | en_US  | success | 0              | 100%  |
      | mobile  | fr_FR  | success | 0              | 100%  |
    When I visit the "Attributes" column tab
    And I visit the "Media" group
    And I attach file "SNKRS-1C-s.png" to "Side view"
    And I save the product
    Then I should be on the product "sneakers" edit page
    When I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | success | 0              | 100%  |
      | tablet  | fr_FR  | warning | 1              | 88%   |
      | mobile  | en_US  | success | 0              | 100%  |
      | mobile  | fr_FR  | success | 0              | 100%  |
