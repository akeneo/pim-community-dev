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
      | First name | John         |
      | Last name  | Smith        |
      | Phone      | +33755337788 |
    And I save the user
    Then I should see the flash message "User saved"
    Then the field First name should contain "John"
    And the field Phone should contain "+33755337788"

  Scenario: Successfully edit and apply user preferences
    When I edit the "Peter" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Catalog locale       | German (Germany)  |
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

  Scenario: Successfully setup user timezone
    Given I edit the "Peter" user
    And I visit the "Interfaces" tab
    Then I should see the text "UTC"
    When I fill in the following information:
      | Timezone | Panama |
    And I save the user
    And I reload the page
    And I visit the "Interfaces" tab
    Then I should see the text "Panama EST (UTC-05:00)"
