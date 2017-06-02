@javascript
Feature: Sort attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to sort options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I visit the "Values" tab
    And I should see the "Options" section
    And I should see "To manage options, please save the attribute first"
    And I save the attribute
    And I should see the flash message "Attribute successfully created"

  Scenario: Auto sorting disable reorder
    Given I check the "Automatic option sorting" switch
    When I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    Then I should not see reorder handles
    And I should see "large_size medium_size small_size"
    When I uncheck the "Automatic option sorting" switch
    Then I should see reorder handles
    And I should see "small_size medium_size large_size"

  Scenario: Display attribute options ordered in PEF
    Given I check the "Automatic option sorting" switch
    When I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    And I save the attribute
    And I should not see the text "There are unsaved changes"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | a_product |
    And I press the "Save" button in the popin
    And I should be on the product "a_product" edit page
    And I add available attributes size
    And I should see the ordered choices [large_size], [medium_size], [small_size] in size
    
