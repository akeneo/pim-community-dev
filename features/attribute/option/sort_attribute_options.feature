@javascript
Feature: Sort attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to sort options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Auto sorting disable reorder
    Given I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I save the attribute
    And I visit the "Options" tab
    When I check the "Sort automatically options by alphabetical order" switch
    And I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    Then I should not see reorder handles
    And I should see the text "large_size medium_size small_size"
    When I uncheck the "Sort automatically options by alphabetical order" switch
    Then I should see reorder handles
    And I should see the text "small_size medium_size large_size"

  Scenario: Display attribute options ordered by code in PEF when no label on options
    Given I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I save the attribute
    And I visit the "Options" tab
    And I check the "Sort automatically options by alphabetical order" switch
    And I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    And I save the attribute
    Then I should not see the text "There are unsaved changes"
    When I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | a_product |
    And I press the "Save" button in the popin
    Then I should be on the product "a_product" edit page
    When I add available attributes size
    Then I should see the ordered choices [large_size], [medium_size], [small_size] in size

  Scenario: Display attribute options ordered by label in PEF
    Given I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I save the attribute
    And I visit the "Options" tab
    And I check the "Sort automatically options by alphabetical order" switch
    And I create the following attribute options:
      | Code         | en_US  | fr_FR  |
      | small_size   | Csmall | Apetit |
      | medium_size  | Bmedium|        |
      | large_size   | Alarge | Cgrand |
      | elarge_size  | Xlarge |        |
    And I save the attribute
    Then I should not see the text "There are unsaved changes"
    When I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | a_product |
    And I press the "Save" button in the popin
    When I am on the "a_product" product page
    And I switch the locale to "en_US"
    And I add available attributes size
    Then I should see the ordered choices Alarge, Bmedium, Csmall, Xlarge in size
    And I switch the locale to "fr_FR"
    Then I should see the ordered choices [elarge_size], [medium_size], Apetit, Cgrand in size
