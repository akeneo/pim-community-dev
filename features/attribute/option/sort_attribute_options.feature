@javascript
Feature: Sort attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to sort options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I am on the "color" attribute page
    And I visit the "Options" tab

  Scenario: Auto sorting disable reorder
    Given I check the "Sort automatically options by alphabetical order" switch
    Then I should not see reorder handles
    And the attribute options order should be black, blue, charcoal, greem, maroon, red, saddle, white
    When I uncheck the "Sort automatically options by alphabetical order" switch
    Then I should see reorder handles
    And the attribute options order should be white, black, blue, maroon, saddle, greem, red, charcoal

  Scenario: Display attribute options ordered in PEF
    Given I check the "Sort automatically options by alphabetical order" switch
    And I save the attribute
    And I should not see the text "There are unsaved changes"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    When I visit the "Colors" group
    Then I should see the ordered choices Black, Blue, Charcoal, Greem, Maroon, Red, Saddle, White in Color

  Scenario: Display attribute options ordered in PEF when there are options without label
    Given I check the "Sort automatically options by alphabetical order" switch
    And I create the following attribute options:
      | Code   |
      | yellow |
      | pink   |
    And I save the attribute
    And I should not see the text "There are unsaved changes"
    And I am on the products page
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    When I visit the "Colors" group
    Then I should see the ordered choices [pink], [yellow], Black, Blue, Charcoal, Greem, Maroon, Red, Saddle, White in Color
