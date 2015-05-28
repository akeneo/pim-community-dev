@javascript
Feature: Enable and disable a product
  In order to avoid exportation of some products I'm still working on
  As a product manager
  I need to be able to enable or disable a product

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @skip-pef @javascript
  Scenario: Successfully disable a product
    Given an enabled "boat" product
    When I am on the "boat" product page
    And I disable the product
    Then I should see flash message "Product working copy has been updated"
    And product "boat" should be disabled

  @skip-pef @javascript
  Scenario: Successfully enable a product
    Given a disabled "boat" product
    When I am on the "boat" product page
    And I enable the product
    Then I should see flash message "Product working copy has been updated"
    And product "boat" should be enabled
