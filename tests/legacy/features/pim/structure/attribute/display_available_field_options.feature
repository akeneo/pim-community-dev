@javascript
Feature: Display available field options
  In order to configure an attribute validation rules
  As a product manager
  I need to see only relevant validation fields given the attribute type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario Outline: Successfully display available parameter fields for attribute types
    Given I am on the attributes page
    When I create a "<type>" attribute
    Then I should see the <fields> fields

    Examples:
      | type       | fields                                                                                 |
      | Identifier | Max characters, Validation rule                                                        |
      | Date       | Min date, Max date                                                                     |
      | File       | Max file size (MB), Allowed extensions                                                 |
      | Image      | Max file size (MB), Allowed extensions                                                 |
      | Metric     | Min number, Max number, Decimal values allowed, Negative values allowed, Metric family |
      | Price      | Min number, Max number, Decimal values allowed                                         |
      | Number     | Min number, Max number, Decimal values allowed, Negative values allowed                |
      | Text Area  | Max characters, Rich text editor enabled                                               |
      | Text       | Max characters, Validation rule                                                        |

  Scenario Outline: Successfully display available values fields for attribute types
    Given I create a "<type>" attribute
    And I fill in the following information:
      | Code            | new_attribute |
      | Attribute group | Other         |
    And I save the attribute
    And I visit the "Options" tab
    Then I should see the <fields> fields

    Examples:
      | type          | fields                                           |
      | Multi select  | Sort automatically options by alphabetical order |
      | Simple select | Sort automatically options by alphabetical order |
