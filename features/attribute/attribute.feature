@javascript
Feature: Display available field options
  In order to create a non edittable attribute
  As a product manager
  I need to see and manage the option 'Is Editbale'

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully display available parameter fields for attribute types
    Then the following attribute types should have the following fields
      | Identifier | Is editable, Max characters, Validation rule                                                                   |
      | Date       | Is editable, Min date, Max date                                                                                |
      | File       | Is editable, Max file size (MB), Allowed extensions                                                            |
      | Image      | Is editable, Max file size (MB), Allowed extensions                                                            |
      | Metric     | Is editable, Min number, Max number, Allow decimals, Allow negative values, Metric family, Default metric unit |
      | Price      | Is editable, Min number, Max number, Allow decimals                                                            |
      | Number     | Is editable, Min number, Max number, Allow decimals, Allow negative values                                     |
      | Text Area  | Is editable, Max characters, WYSIWYG enabled                                                                   |
      | Text       | Is editable, Max characters, Validation rule                                                                   |
