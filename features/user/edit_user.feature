@javascript
Feature: Edit a user
  In order to manage the users and rights
  As an administrator
  I need to be able to edit a user

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit a user
    Given I edit the "admin" user
    When I fill in the following information:
      | First name | John  |
      | Last name  | Smith |
    And I save the user
    Then I should see the flash message "User saved"
    And I should see the text "John Smith"

  Scenario: Successfully edit and apply user preferences
    When I edit the "Peter" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Catalog locale       | de_DE             |
      | Catalog scope        | Print             |
      | Default tree         | 2015 collection   |
      | Product grid filters | SKU, Name, Family |
    And I save the user
    Then I should see the flash message "User saved"
    When I am on the products grid
    And I open the category tree
    Then I should see the text "Kollektion"
    And I should see the text "Drucken"
    And I should see the text "2015 Männer-Kollektion"
    And I should see the text "2015 Damenkollektion"
    And I should see the filters name, family and sku
    And I should not see the filter enabled
