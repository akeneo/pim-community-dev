@javascript
Feature: Display available field options
  In order to configure an attribute validation rules
  As an user
  I need to see only relevant validation fields given the attribute type

  Scenario Outline: Successfully display available parameter fields for attribute types
    Given I am logged in as "admin"
    And I am on the attribute creation page
    When I select the attribute type "<type>"
    Then I should see the <fields> fields

    Examples:
      | type          | fields                                                                                                                       |
      | Identifier    | Max characters, Validation rule, Searchable                                                                                  |
      | Yes/No        | Default value                                                                                                                |
      | Date          | Default value, Date type, Min date, Max date, Searchable                                                                     |
      | File          | Allowed file source, Max file size, Allowed file extensions                                                                  |
      | Image         | Allowed file source, Max file size, Allowed file extensions                                                                  |
      | Metric        | Default value, Min number, Max number, Allow decimals, Allow negative values, Metric family, Default metric unit, Searchable |
      | Price         | Min number, Max number, Allow decimals, Allow negative values, Searchable                                                    |
      | Number        | Default value, Min number, Max number, Allow decimals, Allow negative values, Searchable                                     |
      | Multi select  | Allow automatic value creation, Searchable                                                                    |
      | Simple select | Allow automatic value creation, Searchable                                                                    |
      | Text Area     | Default value, Max characters, WYSIWYG enabled, Searchable                                                                   |
      | Text          | Default value, Max characters, Validation rule, Searchable                                                                   |

  Scenario Outline: Succesfully display available values fields for attribute types
    Given I am logged in as "admin"
    And I am on the attribute creation page
    When I select the attribute type "<type>"
    And I visit the "Values" tab
    Then I should see the <fields> fields

    Examples:
      | type          | fields        |
      | Multi select  | Default value |
      | Simple select | Default value |
