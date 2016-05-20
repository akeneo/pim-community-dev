@javascript
Feature: Display available field options
  In order to create a read only attribute
  As a product manager
  I need to see and manage the option 'Read only'

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully display available parameter fields for attribute types
    Then the following attribute types should have the following fields
      | Identifier | Read only, Max characters, Validation rule                                                                   |
      | Date       | Read only, Min date, Max date                                                                                |
      | File       | Read only, Max file size (MB), Allowed extensions                                                            |
      | Image      | Read only, Max file size (MB), Allowed extensions                                                            |
      | Metric     | Read only, Min number, Max number, Allow decimals, Allow negative values, Metric family, Default metric unit |
      | Price      | Read only, Min number, Max number, Allow decimals                                                            |
      | Number     | Read only, Min number, Max number, Allow decimals, Allow negative values                                     |
      | Text Area  | Read only, Max characters, WYSIWYG enabled                                                                   |
      | Text       | Read only, Max characters, Validation rule                                                                   |

  Scenario: Successfully set attribute to read only
    Given I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    When I am on the "my-jacket" product page
    Then the field Description should be disabled

  Scenario: Successfully set attribute to read only during mass edit
    Given I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    And I am on the products page
    And I mass-edit products my-jacket
    And I am on the "my-jacket" product page
    Then the field Description should be disabled

  Scenario: Successfully display read only attributes
    Given I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    And I am on the products page
    And I mass-edit products my-jacket
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name and Description in group "Product information"
    When I display the Name and Description attributes
    And the field Description should be disabled
