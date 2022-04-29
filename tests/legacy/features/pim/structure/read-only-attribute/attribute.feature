@javascript @read-only-product_attribute-feature-enabled
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
    When I create a "<type>" attribute with code "new_attribute"
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
