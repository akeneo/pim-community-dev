@javascript
Feature: Create enrichment project
  In order to easily enrich a product collection
  As a project creator
  I need to be able to create an enrichment project

  @critical
  Scenario: A project creator can create an enrichment project
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family   | categories        |
      | blue_sandal | Sneakers | summer_collection |
    And I am logged in as "Julia"
    When I am on the products grid
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I open the category tree
    And I filter by "category" with operator "" and value "summer_collection"
    And I collapse the column
    And I display in the products grid the columns sku, name, description
    Then I should be on the products page
    And I should see the text "blue_sandal"
    And I uncollapse the column
    When I click on the create project button
    Then I should see the text "Locale"
    And the field project-locale should contain "English (United States)"
    And the field project-locale should be disabled
    And I should see the text "Channel"
    And the field project-channel should contain "Tablet"
    And the field project-channel should be disabled
    And I should see the text "Name"
    And I should see the text "Due date"
    And I should see the text "Description"
    And I should see the text "Don't worry, project calculation may take a while, so your project may not be visible right away."
    When I fill in the following information in the popin:
      | project-label       | Summer collection 2017                 |
      | project-description | My very awesome summer collection 2007 |
      | project-due-date    | 05/12/2117                             |
    And I press the "Save" button
    Then I should be on the products page
    Then the project "Summer collection 2017" for channel "tablet" and locale "en_US" has the following properties:
      | Label       | Summer collection 2017                 |
      | Description | My very awesome summer collection 2007 |
      | Channel     | tablet                                 |
      | Owner       | Julia                                  |
      | Locale      | en_US                                  |
      | Due date    | 2117-05-12                             |
    And the project "Summer collection 2017" for channel "tablet" and locale "en_US" has a project datagrid view

  Scenario: An error message is displayed to the project creator if label is empty
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products grid
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label | 01/31/2020 |
    And I press the "Save" button
    Then I should see the text "This value should not be blank."

  Scenario: An error message is displayed to the project creator if due date is empty
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products grid
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label | New collection |
    And I press the "Save" button
    Then I should see the text "This value should not be blank."

  Scenario: An error message is displayed to the project creator if the label is greater than 100 characters
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products grid
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label | This is a very long label that has obviously more than one hundred characters which is irrelevant for a normal use |
    Then I should see the text "This value is too long. It should have 100 characters or less."
    And The button "Save" should be disabled
    When I fill in the following information in the popin:
      | project-label | This is a normal label |
    Then I should not see the text "This value is too long. It should have 100 characters or less."
    And The button "Save" should be enabled

  Scenario: An error message is displayed to the project creator if the due date is in the past
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products grid
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-due-date | 10/06/2012 |
    Then I should see the text "You can't select a date in the past."
    And The button "Save" should be disabled
    When I fill in the following information in the popin:
      | project-due-date | 12/30/2099 |
    Then I should not see the text "You can't select a date in the past."
    And The button "Save" should be enabled

  Scenario: An error message is displayed to the project creator if the project label already exists with the same locale and channel
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products grid
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label    | Star Wars Collection |
      | project-due-date | 01/31/2051           |
    And I press the "Save" button
    And I am on the products grid
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label    | Star Wars Collection |
      | project-due-date | 01/31/2051           |
    And I press the "Save" button
    Then I should see the text "This value is already used for a project."
    And I reload the page
    When I am on the products grid
    And I switch the scope to "Mobile"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label    | Star Wars Collection |
      | project-due-date | 01/31/2051           |
    And I press the "Save" button
    Then I should be on the products page
    And the project "Star Wars Collection" for channel "tablet" and locale "en_US" has the following properties:
      | Label       | Star Wars Collection |
      | Description |                      |
      | Channel     | tablet               |
      | Owner       | Julia                |
      | Locale      | en_US                |
      | Due date    | 2051-01-31           |
    And the project "Star Wars Collection" for channel "mobile" and locale "en_US" has the following properties:
      | Label       | Star Wars Collection |
      | Description |                      |
      | Channel     | mobile               |
      | Owner       | Julia                |
      | Locale      | en_US                |
      | Due date    | 2051-01-31           |

    Scenario: I can re-submit new data when there is a validation error
      Given a "footwear" catalog configuration
      And I am logged in as "Julia"
      When I am on the products grid
      And I click on the create project button
      When I fill in the following information in the popin:
        | project-label    | Star Wars Collection |
        | project-due-date | 01/31/2051           |
      And I press the "Save" button
      And I am on the products grid
      And I click on the create project button
      When I fill in the following information in the popin:
        | project-label    | Star Wars Collection |
        | project-due-date | 01/31/2051           |
      And I press the "Save" button
      Then I should see the text "This value is already used for a project."
      And the "project-due-date" field should contain "01/31/2051"
      When I fill in the following information in the popin:
        | project-label    | Star Wars  |
      And I press the "Save" button
      Then I should be on the products page
      And the project "Star Wars" for channel "tablet" and locale "en_US" has the following properties:
        | Label       | Star Wars  |
        | Description |            |
        | Channel     | tablet     |
        | Owner       | Julia      |
        | Locale      | en_US      |
        | Due date    | 2051-01-31 |
