@javascript
Feature: Display the family history
  In order to know who, when and what changes has been made to an family
  As an administrator
  I need to have access to a family history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And the following attribute group:
      | code    | label-en_US |
      | general | General     |
    And the following attributes:
      | label-en_US | group   | type             | code        |
      | Description | General | pim_catalog_text | description |

  Scenario: Successfully create a family and see the history
    Given I am on the families grid
    And I create a new family
    And I fill in the following information in the popin:
      | Code | Flyer |
    And I press the "Save" button
    And I should see the flash message "Family successfully created"
    And I should not see the text "There are unsaved changes."
    And I am on the "Flyer" family page
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value | date |
      | 1       | code     | Flyer | now  |
    When I visit the "Properties" tab
    And I fill in the following information:
      | English (United States) | Fly |
    And I press the "Save" button
    And I should see the flash message "Family successfully updated"
    And I should not see the text "There are unsaved changes."
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property    | value | date |
      | 1       | code        | Flyer | now  |
      | 2       | label-en_US | Fly   | now  |
