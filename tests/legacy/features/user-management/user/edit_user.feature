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

  @jira https://akeneo.atlassian.net/browse/PIM-6901
  Scenario: Successfully edit a user with a new role
    Given I am on the role creation page
    And I fill in the following information:
      | Role (required) | Tata role |
    And I visit the "Permissions" tab
    And I grant rights to resource Edit users
    And I grant rights to resource List users
    Then I save the role
    When I edit the "mary" user
    And I visit the "Groups and roles" tab
    And I fill in the following information:
      | Role | Tata role |
    Then I save the user
    And I logout
    When I am logged in as "Mary"
    And I edit the "admin" user
    Then I should see the text "Admin"
    And I should see the text "Save"

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
