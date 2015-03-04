@javascript
Feature: Display available field options
  In order to configure an attribute validation rules
  As a product manager
  I need to see only relevant validation fields given the attribute type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully display available parameter fields for attribute types
    Then the following attribute types should have the following fields
      | Identifier | Max characters, Validation rule                                                                   |
      | Date       | Min date, Max date                                                                                |
      | File       | Max file size (MB), Allowed extensions                                                            |
      | Image      | Max file size (MB), Allowed extensions                                                            |
      | Metric     | Min number, Max number, Allow decimals, Allow negative values, Metric family, Default metric unit |
      | Price      | Min number, Max number, Allow decimals                                                            |
      | Number     | Min number, Max number, Allow decimals, Allow negative values                                     |
      | Text Area  | Max characters, WYSIWYG enabled                                                                   |
      | Text       | Max characters, Validation rule                                                                   |

  Scenario Outline: Successfully display available values fields for attribute types
    Given I create a "<type>" attribute
    And I visit the "Values" tab
    And I save the attribute
    Then I should see the <fields> fields

    Examples:
      | type          | fields                   |
      | Multi select  | Automatic option sorting |
      | Simple select | Automatic option sorting |
