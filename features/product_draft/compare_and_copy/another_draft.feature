@javascript
Feature: Copy value from a draft to an other
  In order to reuse enrich values from another draft
  As a redactor
  I need to be able to compare and copy values from another draft

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku    | family | categories | name-fr_FR | description-en_US-mobile | description-fr_FR-mobile |
      | tshirt | tees   | tees       | Floup      | City tee                 | T-shirt de ville         |

  Scenario: Successfully copy value from another draft
    Given Mary proposed the following change to "tshirt":
      | field       | value                     | locale | scope  |
      | Name        | That's not my tee anymore | en_US  | mobile |
      | Description | JA !                      | en_US  | mobile |
    And I am logged in as "Julia"
    And I edit the "tshirt" product
    And I open the comparison panel
    And I switch the comparison locale to "en_US"
    And I switch the comparison scope to "mobile"
    And I switch the comparison source to "draft_of_Mary"
    Then the Name comparison value should be "That's not my tee anymore"
    And the Description comparison value should be "JA !"
    And I switch the scope to "tablet"
    When I collapse the column
    And I select all translations
    And I copy selected translations
    And the product Description for scope "tablet" should be "JA !"
    And I switch the comparison source to "working_copy"
    But the Name comparison value should be ""
    And the Description comparison value should be "City tee"
