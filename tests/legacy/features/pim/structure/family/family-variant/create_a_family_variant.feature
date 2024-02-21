@javascript
Feature: Family creation
  In order to better organize my catalog
  As an administrator
  I need to be able to create a family variant

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Peter"
    And I am on the "Accessories" family page

  @critical
  Scenario: Successfully create a new family variant
    Given I visit the "Variants" tab
    When I open the family variant creation form
    And I fill in "code" with "accessories_color_size"
    And I fill in "label" with "Accessories by color and size"
    And I fill in "numberOfLevels" with "2"
    And I fill in the following information in the popin:
      | Variant axis level 1 (required) | Color |
      | Variant axis level 2 (required) | Size  |
    And I press the "Create" button in the popin
    Then I should see the text "Family variant successfully created"
    Then I should see the text "Drag & drop attributes to the selected variant level"

  @critical
  Scenario: Successfully validate a family variant
    Given I visit the "Variants" tab
    When I open the family variant creation form
    And I fill in "code" with "invalid code?"
    And I fill in "label" with "This label is too long. There are are more than 100 characters in this string. It is not a valid label."
    And I fill in "numberOfLevels" with "2"
    And I press the "Create" button in the popin
    Then I should see the text "Family variant code may contain only letters, numbers and underscores"
    And I should see the text "There should be at least one attribute defined as axis for the attribute set for level \"1\""
    And I should see the text "There should be at least one attribute defined as axis for the attribute set for level \"2\""
    And I should see the text "This value is too long. It should have 100 characters or less."
    When I fill in "code" with "valid_code"
    And I fill in "label" with "Accessories by color and size"
    And I fill in "numberOfLevels" with "2"
    And I fill in the following information in the popin:
      | Variant axis level 1 (required) | Color, Size |
      | Variant axis level 2 (required) | Size        |
    And I press the "Create" button in the popin
    Then I should see the text "Variant axes must be unique, \"size\" are used several times in variant attributes sets"
    But I should not see the text "Family variant code may contain only letters, numbers and underscores"
