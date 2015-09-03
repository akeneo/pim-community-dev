@javascript
Feature: Edit a user
  In order to manage the users and rights
  As an administrator
  I need to be able to edit a user

  Background:
    Given the "apparel" catalog configuration
    And the following assets categories:
      | code       | label-en_US | parent   |
      | tractors   | Tractors    |          |
      | john_deere | John Deere  | tractors |
    And the following asset category accesses:
      | asset category | user group | access |
      |  tractors      | Manager    | view   |
      | john_deere     | Manager    | view   |
    And I am logged in as "Peter"

  @javascript
  Scenario: Product grid filters preference applies on the published product grid
    When I edit the "Peter" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Product grid filters | SKU, Name, Family |
    And I save the user
    When I am on the published index page
    And I should see the filters Name, Family and SKU
    And I should not see the filters Status

  @javascript
  Scenario: Successfully edit and apply user preferences
    When I edit the "Peter" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Default asset tree | Tractors |
    And I save the user
    When I am on the assets categories page
    And I should see "Tractors"
    And I should see "John deere"
