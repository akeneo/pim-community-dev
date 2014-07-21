@javascript
Feature: Change status of many products at once
  In order to include or exclude many products in or from the export
  As a product manager
  I need to be able to change the status of many products at once

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Enable many products at once
    Given a disabled "boat" product
    And a disabled "jet-ski" product
    And I am on the products page
    When I mass-edit products boat and jet-ski
    And I choose the "Change status (enable / disable)" operation
    And I enable the products
    Then product "boat" should be enabled
    And product "jet-ski" should be enabled

  Scenario: Disable many products at once
    Given an enabled "boat" product
    And an enabled "jet-ski" product
    And I am on the products page
    When I mass-edit products boat and jet-ski
    And I choose the "Change status (enable / disable)" operation
    And I disable the products
    Then product "boat" should be disabled
    And product "jet-ski" should be disabled
