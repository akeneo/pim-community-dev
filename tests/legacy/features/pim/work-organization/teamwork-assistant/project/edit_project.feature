@javascript
Feature: Edit basic project informations
  In order to easily have project with consistent informations
  As a project creator
  I need to be able to edit basic project informations

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family   | categories        |
      | blue_sandal | Sneakers | summer_collection |
    And I am logged in as "Julia"
    And I am on the products grid
    And I click on the create project button
    And I fill in the following information in the popin:
      | project-label    | Star Wars Collection |
      | project-due-date | 01/31/2051           |
    And I press the "Save" button
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I logout

  Scenario: A project creator can edit his project
    Given I am logged in as "Julia"
    And I am on the products grid
    And I switch view selector type to "Projects"
    And I should see the text "Star Wars Collection"
    When I click on the edit project button
    Then the field project-locale should contain "English (United States)"
    And the field project-locale should be disabled
    And the field project-channel should contain "Tablet"
    And the field project-channel should be disabled
    When I fill in the following information in the popin:
      | project-label       | Star Wars: Rogue One Collection |
      | project-due-date    | 05/20/2051                      |
      | project-description | A rebellion built on hope       |
    And I press the "Save" button
    Then I should be on the products page
    And I should be on the view "Star Wars: Rogue One Collection"
    And the project "Star Wars: Rogue One Collection" for channel "tablet" and locale "en_US" has the following properties:
      | Label       | Star Wars: Rogue One Collection |
      | Description | A rebellion built on hope       |
      | Due date    | 2051-05-20                      |

  Scenario: A contributor doesn't see the button to edit a project if he's not the creator
    Given I am logged in as "Mary"
    And I am on the products grid
    When I switch view selector type to "Projects"
    Then I should see the text "Star Wars Collection"
    But I should not see the "Edit project" icon button

  Scenario: Project edition doesn't have same validation rules than project creation
    Given I am logged in as "Julia"
    And I am on the products grid
    And I click on the create project button
    And I fill in the following information in the popin:
      | project-label    | Existing project |
      | project-due-date | 01/02/2051       |
    And I press the "Save" button
    And I am on the products grid
    And I switch view selector type to "Projects"
    And I apply the "Star Wars Collection" project
    Then I should be on the view "Star Wars Collection"
    When I click on the edit project button
    And I fill in the following information in the popin:
      | project-label       |                           |
      | project-due-date    | 05/20/2051                |
      | project-description | A rebellion built on hope |
    And I press the "Save" button
    Then I should see a project validation error "This value should not be blank."
    When I fill in the following information in the popin:
      | project-label       | This is a very long label that has obviously more than one hundred characters which is irrelevant for a normal use |
      | project-due-date    | 05/20/2051                                                                                                         |
      | project-description | A rebellion built on hope                                                                                          |
    Then I should see a project validation error "This value is too long. It should have 100 characters or less."
    When I fill in the following information in the popin:
      | project-label       | Star Wars: Rogue One Collection |
      | project-due-date    |                                 |
      | project-description | A rebellion built on hope       |
    And I press the "Save" button
    Then I should see a project validation error "This value should not be blank."
    When I fill in the following information in the popin:
      | project-label       | Existing project          |
      | project-due-date    | 05/20/2051                |
      | project-description | A rebellion built on hope |
    And I press the "Save" button
    Then I should see a project validation error "This value is already used for a project."
    When I fill in the following information in the popin:
      | project-label       | Star Wars: Rogue One Collection |
      | project-due-date    | 12/30/1990                      |
      | project-description | A rebellion built on hope       |
    And I press the "Save" button
    Then I should be on the products page
    And I should be on the view "Star Wars: Rogue One Collection"
    And the project "Star Wars: Rogue One Collection" for channel "tablet" and locale "en_US" has the following properties:
      | Label       | Star Wars: Rogue One Collection |
      | Description | A rebellion built on hope       |
      | Due date    | 1990-12-30                      |
