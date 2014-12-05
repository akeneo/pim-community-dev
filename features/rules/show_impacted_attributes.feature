@javascript
Feature: On a product edit/show display impacted attributes
  In order to know which attributes are affected or not
  As a regular user
  I need to see which attributes are affected by a rule or not

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"
    And the following products:
      | sku       | family  |
      | my-loafer | sandals |
    And the following product rules:
      | code  | priority | impacted_attribute |
      | rule1 | 10       | description        |

  Scenario: Successfully create, edit and save a product
    Given I am on the "my-loafer" product page
    And I should see an "i.icon-code-fork" element
    Then I should see the smart attribute tooltip
