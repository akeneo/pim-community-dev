@javascript
Feature: Enable and disable a product
  In order to avoid exportation of some products I'm still working on
  As a product manager
  I need to be able to enable or disable a product

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully disable a product
    Given an enabled "boat" product
    When I am on the "boat" product page
    And I disable the product
    Then product "boat" should be disabled
