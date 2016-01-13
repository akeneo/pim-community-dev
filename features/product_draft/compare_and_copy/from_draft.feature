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
    Then the Name copy value for scope "mobile", locale "en_US" and source "draft" should be "That's not my tee anymore"
    And the Description copy value for scope "mobile", locale "en_US" and source "draft" should be "JA !"
    And I switch the scope to "tablet"
    When I select all translations
    And I copy selected translations
    And the product Description for scope "tablet" should be "JA !"
    But the Name copy value for scope "mobile", locale "en_US" and source "working_copy" should be ""
    And the Description copy value for scope "mobile", locale "en_US" and source "working_copy" should be "City tee"
