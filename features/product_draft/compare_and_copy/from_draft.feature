@javascript
Feature: Copy value from my draft
  In order to reuse enrich values in other languages
  As a redactor
  I need to be able to copy values from my draft

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku    | family | categories | name-fr_FR | description-en_US-mobile | description-fr_FR-mobile |
      | tshirt | tees   | tees       | Floup      | City tee                 | T-shirt de ville         |
    And I am logged in as "Mary"
    And I edit the "tshirt" product

  Scenario: Successfully copy value from my draft
    Given I change the Name to "That's my tee"
    And I change the Description to "Hiking tee"
    And I save the product
    And I change the Name to "That's not my tee anymore"
    And I change the Description to "JA !"
    And I open the comparison panel
    And I switch the comparison locale to "en_US"
    And I switch the comparison scope to "mobile"
    And I switch the comparison source to "draft"
    Then the Name comparison value should be "That's not my tee anymore"
    And the Description comparison value should be "JA !"
    And I switch the scope to "tablet"
    When I select all translations
    And I copy selected translations
    And the product Description for scope "tablet" should be "JA !"
    And I switch the comparison source to "working_copy"
    But the Name comparison value should be ""
    And the Description comparison value should be "City tee"
