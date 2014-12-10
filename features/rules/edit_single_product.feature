
Feature: Edit a single product with rules appliance
  In order ease the enrichement of the catalog
  As a regular user
  I need that the relevant rules are executed when I edit and save a product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"
    And the following products:
      | sku       | family  |
      | my-loafer | sandals |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value     |
      | rule1 | sku   | =        | my-loafer |
    And the following product rule setter actions:
      | rule  | field  | value     | locale |
      | rule1 | name   | My loafer | en_US  |

  Scenario: Successfully create, edit and save a product
    Given the product rule "rule1" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "My loafer"
