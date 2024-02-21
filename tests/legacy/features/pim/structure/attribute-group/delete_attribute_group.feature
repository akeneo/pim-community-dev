@javascript
Feature: Attribute group creation
  In order to organize attributes into group
  As Julia
  I need to be able to delete an attribute group

  Background:
    Given the "default" catalog configuration
    And the following attribute group:
      | code   | label-en_US |
      | sizes  | Sizes       |
      | colors | Colors      |
    And the following attributes:
      | code | group  | type             |
      | red  | colors | pim_catalog_text |
    And I am logged in as "Julia"

  Scenario: Successfully delete an attribute group
    Given I am on the "sizes" attribute group page
    When I press the secondary action "Delete"
    And I press the "Delete" button
    Then I should see the flash message "The deletion has started. You will get a notification once the job is completed. In the meantime, you can keep an eye on its progress on the process tracker."
