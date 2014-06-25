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
      | Identifier | Max characters, Validation rule                                                                                  |
      | Yes/No     | Default value                                                                                                    |
      | Date       | Default value, Min date, Max date                                                                                |
      | File       | Max file size (MB), Allowed extensions                                                                           |
      | Image      | Max file size (MB), Allowed extensions                                                                           |
      | Metric     | Default value, Min number, Max number, Allow decimals, Allow negative values, Metric family, Default metric unit |
      | Price      | Min number, Max number, Allow decimals                                                                           |
      | Number     | Default value, Min number, Max number, Allow decimals, Allow negative values                                     |
      | Text Area  | Default value, Max characters, WYSIWYG enabled                                                                   |
      | Text       | Default value, Max characters, Validation rule                                                                   |

  Scenario Outline: Succesfully display available values fields for attribute types
    Given I create a "<type>" attribute
    And I visit the "Values" tab
    Then I should see the <fields> fields

    Examples:
      | type          | fields                         |
      | Multi select  | Code, Automatic option sorting |
      | Simple select | Code, Automatic option sorting |
