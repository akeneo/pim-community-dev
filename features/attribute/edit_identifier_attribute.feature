Feature: Edit an identifier attribute
  In order to specify options for the identifier
  As a user
  I need to edit an identifier attribute

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully display the identifier related fields
    Given I am on the "SKU" attribute page
    Then I should see the Max characters, Validation rule and Searchable fields
    And the fields Unique, Scope, Usable as grid column and Usable as grid filter should be disabled

  @javascript
  Scenario: Succesfully display a message that an identifier already exists when trying to create a second identifier
    Given I am on the attribute creation page
    When I change the "Attribute type" to "Identifier"
    Then I should see validation error "An identifier attribute already exists."

  @javascript
  Scenario: Fail to create a second identifier
    Given I am on the attribute creation page
    When I change the "Attribute type" to "Identifier"
    And I fill in the following information:
      | Code           | mySecondId |
      | Max characters | 100        |
    And I press the "Save" button
    And I visit the "Parameters" tab
    Then I should see validation error "An identifier attribute already exists."

  @javascript
  Scenario: Successfully edit an identifier attribute
    Given I am on the "SKU" attribute page
    When I fill in the following information:
      | Max characters | 199 |
    And I press the "Save" button
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property       | value |
      | 1       | max_characters | 199   |

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "SKU" attribute page
    And I change the "Validation rule" to "Regular expression"
    Then I should see "There are unsaved changes."
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                      |
      | content | You will lose changes to the attribute if you leave this page. |
