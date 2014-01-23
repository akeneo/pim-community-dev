@javascript
Feature: Delete many product at once
  In order to easily manage products
  As Julia
  I need to be able to remove many products at once

  Background:
    Given the "default" catalog configuration
    And the following products:
      | sku  |
      | pim  |
      | pam  |
      | poum |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Remove many products
    Given I mass-delete products pim, pam, poum
    Then I should see "Are you sure you want to delete selected products?"
    When I confirm the removal
    Then I should not see product pim
    And I should not see product pam
    And I should not see product poum
