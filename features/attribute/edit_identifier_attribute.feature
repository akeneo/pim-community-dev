@javascript
Feature: Edit an identifier attribute
  In order to specify options for the identifier
  As a product manager
  I need to edit an identifier attribute

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display the identifier related fields
    Given I am on the "SKU" attribute page
    Then I should see the Max characters and Validation rule fields
    And the fields Unique, Scope and Usable in grid should be disabled

  Scenario: Fail to create a second identifier attribute
    Given I am on the attributes page
    When I create an "Identifier" attribute
    And I fill in the following information:
      | Code            | mySecondId |
      | Max characters  | 100        |
      | Attribute group | Other      |
    And I press the "Save" button
    And I visit the "Parameters" tab
    Then I should see validation error "An identifier attribute already exists."

  Scenario: Successfully edit an identifier attribute
    Given I am on the "SKU" attribute page
    When I fill in the following information:
      | Max characters | 199 |
    And I press the "Save" button
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property       | value |
      | 2       | max_characters | 199   |

  Scenario: Fail to edit a text attribute when the group is missing
    Given I am on the "SKU" attribute page
    And I fill in the following information:
      | Attribute group |  |
    And I save the attribute
    Then I should see validation error "This value should not be blank."

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "SKU" attribute page
    And I change the "Validation rule" to "Regular expression"
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                      |
      | content | You will lose changes to the attribute if you leave this page. |

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "SKU" attribute page
    And I change the "Validation rule" to "Regular expression"
    Then I should see the text "There are unsaved changes."

  Scenario: Successfully retrieve the last visited tab
    Given I am on the "SKU" attribute page
    And I visit the "History" tab
    And I am on the products page
    Then I am on the "SKU" attribute page
    And I should see the text "version"
    And I should see the text "author"

  Scenario: Successfully retrieve the last visited tab after a save
    Given I am on the "SKU" attribute page
    And I visit the "History" tab
    Then I save the "attribute"
    And I should see the text "version"
    And I should see the text "author"
