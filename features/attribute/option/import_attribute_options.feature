Feature: Import attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to import options for attributes

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | fruit |
      | Attribute group | Other |
    And I save the attribute

  @javascript
  Scenario: Successfully show default translation when blank text
    Given the following CSV file to import:
      """
      code;attribute;sort_order;label-en_US
      kiwi;fruit;0;
      Converse;manufacturer;0;
      """
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_option_import" job to finish
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | caterpillar |
    And I press the "Save" button in the popin
    And I should be on the product "caterpillar" edit page
    And I am on the "caterpillar" product page
    When I add available attributes fruit
    And I change the "[fruit]" to "[kiwi]"
    Then I should see the text "[kiwi]"
    When I add available attributes Manufacturer
    And I change the "Manufacturer" to "[Converse]"
    Then I should see the text "[Converse]"
