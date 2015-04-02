@javascript
Feature: Configure action to change status of many products at once
  In order to configure mass edit change status on many products
  As a product manager
  I need to be able to configure mass edit action via a form

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Julia"

  Scenario: Configure the operation to enable many products at once
    Given a disabled "boat" product
    And a disabled "jet-ski" product
    And I am on the products page
    When I mass-edit products boat and jet-ski
    And I choose the "Change status (enable / disable)" operation
    And I enable the products
    And I apply the following mass-edit operation with the given configuration:
      | operation     | filters                                                          | actions                               |
      | change-family | [{"field":"sku", "operator":"IN", "value": ["boat", "jet-ski"]}] | [{"field": "enabled", "value": true}] |
    Then product "boat" should be enabled
    And product "jet-ski" should be enabled

  Scenario: Configure the operation to disable many products at once
    Given an enabled "boat" product
    And an enabled "jet-ski" product
    And I am on the products page
    When I mass-edit products boat and jet-ski
    And I choose the "Change status (enable / disable)" operation
    And I disable the products
    And I apply the following mass-edit operation with the given configuration:
      | operation     | filters                                                          | actions                                |
      | change-family | [{"field":"sku", "operator":"IN", "value": ["boat", "jet-ski"]}] | [{"field": "enabled", "value": false}] |
    Then product "boat" should be disabled
    And product "jet-ski" should be disabled
