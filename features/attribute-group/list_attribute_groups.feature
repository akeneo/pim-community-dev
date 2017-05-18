@javascript
Feature: List attribute groups
  In order to see attribute groups in my catalog
  As a product manager
  I need to be able to list existing attribute groups

  Scenario: Successfully display attribute groups
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the attribute groups page
    Then I should see the text "Product information"
    Then I should see the text "Marketing"

  Scenario: Order attribute group choices on the attribute creation page
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Text" attribute
    Then I should see select choices of the "Attribute group" in the following order:
      """
      Product information,Marketing,Sizes,Colors,Media,Other
      """
    Given the following XLSX file to import:
      """
      code;sort_order
      sizes;1
      marketing;2
      info;3
      colors;4
      other;5
      media;6
      """
    And the following job "xlsx_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_attribute_group_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_attribute_group_import" job to finish
    And I am on the attributes page
    And I create a "Text" attribute
    Then I should see select choices of the "Attribute group" in the following order:
      """
      Sizes,Marketing,Product information,Colors,Other,Media
      """
