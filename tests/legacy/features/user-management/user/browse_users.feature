@javascript
Feature: Browse users
  In order to manage the users and rights
  As an administrator
  I need to be able to see users

  Background:
    Given the "default" catalog configuration
    And there is a "magento" connection
    And I am logged in as "Peter"

  Scenario: Successfully display users
    Given I am on the users page
    Then I should see users "admin", "Peter", "Julia", "Mary" and "Sandra"
    But I should not see users "magento"
