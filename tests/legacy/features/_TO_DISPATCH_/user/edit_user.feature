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
      | tractors       | IT support | view   |
      | john_deere     | IT support | view   |
    And I am logged in as "Peter"

  Scenario: Product grid filters preference applies on the published product grid
    When I edit the "Peter" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Product grid filters | SKU, Name, Family |
    And I save the user
    When I am on the published products grid
    And I should see the filters name, family and sku
    And I should not see the filters Status

  Scenario: Successfully edit and apply user preferences
    When I edit the "Peter" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Default asset tree | Tractors |
    And I save the user
    When I am on the assets categories page
    And I should see the text "Tractors"
    And I should see the text "John deere"
