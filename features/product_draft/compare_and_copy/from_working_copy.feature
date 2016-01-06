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
    And I edit the "tshirt" product

  Scenario: Successfully copy value from working copy
    Given I change the Name to "That's my tee"
    And I change the Description to "Hiking tee"
    Then the Name copy value for scope "mobile", locale "en_US" and source "working_copy" should be ""
    And the Description copy value for scope "mobile", locale "en_US" and source "working_copy" should be "City tee"
    When I select all translations
    And I copy selected translations
    Then the product Name should be ""
    And the product Description for scope "mobile" should be "City tee"
