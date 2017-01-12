@javascript
Feature: Create enrichment project
  In order to easily enrich a product collection
  As a project creator
  I need to be able to create an enrichment project

  Scenario: A project creator can create an enrichment project
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family   | categories        |
      | blue_sandal | Sneakers | summer_collection |
    And I am logged in as "Julia"
    When I am on the products page
    And I filter by "family" with operator "in list" and value "Sneakers"
    And I filter by "category" with operator "" and value "summer_collection"
    And I display in the products grid the columns sku, name, description
    And I should be on the products page
    And I click on the create project button
    Then I should see the text "Label"
    Then I should see the text "Due date"
    Then I should see the text "Description"
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
    When I am on the products page
    And I click on the create project button
    When I fill in the following information in the popin:
      | Due Date | 01/31/2020 |
    And I press the "Save" button
    Then I should see the text "This value should not be blank."

  Scenario: An error message is displayed to the project creator if due date is empty
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products page
    And I click on the create project button
    When I fill in the following information in the popin:
      | Label | New collection |
    And I press the "Save" button
    Then I should see the text "This value should not be blank."

  Scenario: An error message is displayed to the project creator if the label is greater than 100 characters
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products page
    And I click on the create project button
    When I fill in the following information in the popin:
      | Label | This is a very long label that has obviously more than one hundred characters which is irrelevant for a normal use |
    Then I should see the text "This value is too long. It should have 100 characters or less."
    And The button "Save" should be disabled
    When I fill in the following information in the popin:
      | Label | This is a normal label |
    Then I should not see the text "This value is too long. It should have 100 characters or less."
    And The button "Save" should be enabled

  Scenario: An error message is displayed to the project creator if the due date is in the past
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products page
    And I click on the create project button
    When I fill in the following information in the popin:
      | Due Date | 10/06/2012 |
    Then I should see the text "You can't select a date in the past."
    And The button "Save" should be disabled
    When I fill in the following information in the popin:
      | Due Date | 12/30/2099 |
    Then I should not see the text "You can't select a date in the past."
    And The button "Save" should be enabled

  Scenario: An error message is displayed to the project creator if the project label already exists with the same locale and channel
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the products page
    And I click on the create project button
    When I fill in the following information in the popin:
      | Label    | Star Wars Collection |
      | Due Date | 01/31/2051           |
    And I press the "Save" button
    And I am on the products page
    And I click on the create project button
    When I fill in the following information in the popin:
      | Label    | Star Wars Collection |
      | Due Date | 01/31/2051           |
    And I press the "Save" button
    Then I should see the text "This value is already used."
    When I am on the products page
    And I filter by "scope" with operator "equals" and value "Mobile"
    And I click on the create project button
    When I fill in the following information in the popin:
      | Label    | Star Wars Collection |
      | Due Date | 01/31/2051           |
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
