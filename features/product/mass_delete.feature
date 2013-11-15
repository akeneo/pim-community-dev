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
    Then I should see "Execution confirmation"
    And I confirm the removal
    Then I should not see product pim
    Then I should not see product pam
    Then I should not see product poum
