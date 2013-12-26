@javascript
Feature: Define user rights
  In order to assign or remove some rights to a group of users
  As an admin
  I need to be able to assign/remove rights

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully edit and apply user rights
    Given I am on the "Administrator" role page
    When I remove rights to List products and List channels
    And I save the role
    Then I should be on the "Administrator" role page
    And I should see "List products None"
    And I should see "List channels None"
    And I should not be able to access the products page
    And I should not be able to access the channels page
    But I should be able to access the attributes page
