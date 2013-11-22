@javascript
Feature: Display the completeness of a product
  In order to see the completeness of a product in the catalog
  As a user
  I need to be able to display the completeness of a product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And the following products:
      | sku      | family   |
      | sneakers | sneakers |
      | sandals  | sandals  |
    And the following product values:
      | product  | attribute          | value                 | locale | scope  |
      | sneakers | name               | Sneakers              | en_US  |        |
      | sneakers | name               | Espadrilles           | fr_FR  |        |
      | sneakers | manufacturer       | Converse              |        |        |
      | sneakers | weather_conditions | hot                   |        |        |
      | sneakers | description        | Great sneakers        | en_US  | mobile |
      | sneakers | description        | Really great sneakers | en_US  | tablet |
      | sneakers | description        | Grandes espadrilles   | fr_FR  | mobile |
      | sneakers | price              | 69 EUR, 99 USD        |        |        |
      | sneakers | rating             | 4                     |        |        |
      | sneakers | size               | 43                    |        |        |
      | sneakers | color              | blue                  |        |        |
      | sneakers | lace_color         | white                 |        |        |
      | sandals  | name               | Sandales              | fr_FR  |        |
      | sandals  | color              | white                 |        |        |
      | sandals  | description        | Super sandales        | fr_FR  | tablet |
      | sandals  | description        | Super sandales        | fr_FR  | mobile |
    And I am logged in as "admin"
    And I launched the completeness calculator

  Scenario: Successfully display the completeness of the product
    Given I am on the "sneakers" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | success | Complete         | 100%  |
      | mobile  | French (France)         | success | Complete         | 100%  |
      | tablet  | English (United States) | warning | 1 missing value  | 89%   |
      | tablet  | French (France)         | warning | 2 missing values | 78%   |

  Scenario: Successfully display the completeness of the second product
    Given I am on the "sandals" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | warning | 3 missing values | 40%   |
      | mobile  | French (France)         | warning | 2 missing values | 60%   |
      | tablet  | English (United States) | warning | 6 missing values | 25%   |
      | tablet  | French (France)         | warning | 4 missing values | 50%   |
