@javascript
Feature: Display available field options
  In order to configure an attribute validation rules
  As a user
  I need to see only relevant validation fields given the attribute type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the attribute creation page

  Scenario: Successfully display available parameter fields for attribute types
    Then the following attribute types should have the following fields
      | Identifier    | Max characters, Validation rule, Searchable                                                                                  |
      | Yes/No        | Default value                                                                                                                |
      | Date          | Default value, Date type, Min date, Max date, Searchable                                                                     |
      | File          | Max file size (MB), Allowed extensions                                                                                       |
      | Image         | Max file size (MB), Allowed extensions                                                                                       |
      | Metric        | Default value, Min number, Max number, Allow decimals, Allow negative values, Metric family, Default metric unit, Searchable |
      | Price         | Min number, Max number, Allow decimals, Searchable                                                                           |
      | Number        | Default value, Min number, Max number, Allow decimals, Allow negative values, Searchable                                     |
      | Multi select  | Searchable                                                                                                                   |
      | Simple select | Searchable                                                                                                                   |
      | Text Area     | Default value, Max characters, WYSIWYG enabled, Searchable                                                                   |
      | Text          | Default value, Max characters, Validation rule, Searchable                                                                   |

  Scenario Outline: Succesfully display available values fields for attribute types
    Given I change the "Attribute type" to "<type>"
    And I visit the "Values" tab
    Then I should see the <fields> fields

    Examples:
      | type          | fields |
      | Multi select  | Code   |
      | Simple select | Code   |
