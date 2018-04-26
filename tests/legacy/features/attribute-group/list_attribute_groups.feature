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

  @jira https://akeneo.atlassian.net/browse/PIM-6436
  Scenario: Sucessfully display the attribute groups in the PEF without limit
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attributes;sort_order
      attribute_group_1;sku;101
      attribute_group_2;name;102
      attribute_group_3;manufacturer;124
      attribute_group_4;weather_conditions;104
      attribute_group_5;description;105
      attribute_group_6;comment;106
      attribute_group_7;price;107
      attribute_group_8;rating;108
      attribute_group_9;side_view;109
      attribute_group_10;top_view;110
      attribute_group_11;size;111
      attribute_group_12;color;112
      attribute_group_13;lace_color;200
      attribute_group_14;length;114
      attribute_group_15;volume;115
      attribute_group_16;number_in_stock;116
      attribute_group_17;destocking_date;117
      attribute_group_18;handmade;118
      attribute_group_19;heel_color;119
      attribute_group_20;sole_color;125
      attribute_group_21;cap_color;121
      attribute_group_22;sole_fabric;122
      """
    And the following job "csv_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_attribute_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_group_import" job to finish
    And I am on the families grid
    And I create a new family
    And I fill in the following information in the popin:
      | Code | big_family |
    And I press the "Save" button in the popin
    And I wait to be on the "big_family" family page
    And I visit the "Attributes" tab
    And I add attributes by group "attribute_group_1, attribute_group_2, attribute_group_3, attribute_group_4, attribute_group_5, attribute_group_6, attribute_group_7, attribute_group_8, attribute_group_9, attribute_group_10"
    And I add attributes by group "attribute_group_11, attribute_group_12, attribute_group_13, attribute_group_14, attribute_group_15, attribute_group_16, attribute_group_17, attribute_group_18, attribute_group_19, attribute_group_20"
    And I add attributes by group "attribute_group_21, attribute_group_22"
    And I should see the text "attribute_group_1"
    And I should see the text "attribute_group_11"
    And I should see the text "attribute_group_22"
    And I should see the text "There are unsaved changes"
    And I save the family
    And I should not see the text "There are unsaved changes"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | a_boot       |
      | Family | [big_family] |
    And I press the "Save" button in the popin
    And I wait to be on the "a_boot" product page
    Then I should see the text "[attribute_group_1]"
    And I should see the text "[attribute_group_22]"
    And the order of groups should be "[attribute_group_1], [attribute_group_2], [attribute_group_4], [attribute_group_5], [attribute_group_6], [attribute_group_7], [attribute_group_8], [attribute_group_9], [attribute_group_10], [attribute_group_11], [attribute_group_12], [attribute_group_14], [attribute_group_15], [attribute_group_16], [attribute_group_17], [attribute_group_18], [attribute_group_19], [attribute_group_21], [attribute_group_22], [attribute_group_3], [attribute_group_20], [attribute_group_13]"
