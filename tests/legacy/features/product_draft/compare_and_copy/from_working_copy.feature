@javascript
Feature: Compare and copy from working copy
  In order to reuse enrich values from the working copy
  As a redactor
  I need to be able to compare and copy values from the working copy

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku    | family | categories | name-fr_FR | description-en_US-mobile | description-fr_FR-mobile |
      | tshirt | tees   | tees       | Floup      | City tee                 | T-shirt de ville         |
    And I am logged in as "Mary"

  Scenario: Successfully copy value from working copy
    Given I edit the "tshirt" product
    And I change the Name to "That's my tee"
    And I change the Description to "Hiking tee"
    And I open the comparison panel
    And I switch the comparison locale to "en_US"
    And I wait 1 seconds
    And I switch the comparison scope to "mobile"
    And I wait 1 seconds
    And I switch the comparison source to "working_copy"
    And I wait 1 seconds
    Then the Name comparison value should be ""
    And the Description comparison value should be "City tee"
    When I select all translations
    And I copy selected translations
    Then the product Name should be ""
    And the product Description for scope "mobile" should be "City tee"
