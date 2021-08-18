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

  Scenario Outline: Successfully display available parameter fields for attribute types
    Given I am on the attributes page
    When I create a "<type>" attribute
    Then I should see the <fields> fields

    Examples:
      | type        | fields                                                                                                 |
      | Identifier  | Read only, Max characters, Validation rule                                                             |
      | Date        | Read only, Min date, Max date                                                                          |
      | File        | Read only, Max file size (MB), Allowed extensions                                                      |
      | Image       | Read only, Max file size (MB), Allowed extensions                                                      |
      | Measurement | Read only, Min number, Max number, Decimal values allowed, Negative values allowed, Measurement family |
      | Price       | Read only, Min number, Max number, Decimal values allowed                                              |
      | Number      | Read only, Min number, Max number, Decimal values allowed, Negative values allowed                     |
      | Text Area   | Read only, Max characters, Rich text editor enabled                                                    |
      | Text        | Read only, Max characters, Validation rule                                                             |

  Scenario: Successfully set attribute to read only
    Given I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    And I should not see the text "There are unsaved change"
    When I am on the "my-jacket" product page
    Then the field Description should be disabled

  @skip @info it fails but it is a bug
  Scenario: Error dialog displayed when deleting an attribute linked to a published product from the attribute page
    Given I am on the "my-jacket" product page
    And I fill in the following information:
      | Name | My Jacket |
    And I press the "Save" button
    And I publish the product "my-jacket"
    And I am on the "name" attribute page
    When I press the secondary action "Delete"
    And I confirm the removal
    Then I should see the text "Cannot delete this attribute"

  @skip @info it fails but it is a bug
  Scenario: Error dialog displayed when deleting an attribute linked to a published product from the attribute grid page
    Given I am on the "my-jacket" product page
    And I fill in the following information:
      | Name | My Jacket |
    And I press the "Save" button
    And I publish the product "my-jacket"
    And I am on the attributes page
    When I click on the "Delete" action of the row which contains "Name"
    And I confirm the removal
    Then I should see the text "Cannot delete this attribute"

  @skip @info To be fixed in TIP-764
  Scenario: Successfully set attribute to read only during mass edit
    Given I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    And I should not see the text "There are unsaved change"
    And I am on the products grid
    When I select row my-jacket
    And I press the "Bulk actions" button
    And I choose the "Edit attribute values" operation
    And I display the Description attribute
    Then the field Description should be disabled

  @skip @info To be fixed in TIP-764
  Scenario: Successfully display read only attributes
    Given I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    And I should not see the text "There are unsaved change"
    And I am on the products grid
    When I select rows my-jacket
    And I press the "Bulk actions" button
    And I choose the "Edit attribute values" operation
    Then I should see available attributes Name and Description in group "Product information"
    When I display the Name and Description attributes
    Then the field Description should be disabled
