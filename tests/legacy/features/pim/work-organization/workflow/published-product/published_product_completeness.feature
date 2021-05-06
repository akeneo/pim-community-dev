@javascript
Feature: View the completeness of a published product
  In order to know the completeness of a published product
  As a contributor
  I need to be able to display the completeness tab on a published product view

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
    And I publish the product "sneakers"
    And I publish the product "sandals"

  @jira https://akeneo.atlassian.net/browse/PIM-5136
  Scenario: Successfully display the completeness of the products
    Given I am on the "sneakers" published product show page
    When I visit the "Completeness" column tab
    And I should see the completeness:
      | channel | locale                  | ratio |
      | Tablet  | English (United States) | 88 %  |
      | Tablet  | French (France)         | 77 %  |
      | Mobile  | English (United States) | 100 % |
      | Mobile  | French (France)         | 100 % |
    When I am on the "sandals" published product show page
    And I visit the "Completeness" column tab
    And I should see the completeness:
      | channel | locale                  | ratio |
      | Tablet  | English (United States) | 25 %  |
      | Tablet  | French (France)         | 50 %  |
      | Mobile  | English (United States) | 40 %  |
      | Mobile  | French (France)         | 60 %  |
