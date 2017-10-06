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
    And I visit the "Values" tab
    Then I should see the "Options" section
    And I should see "To manage options, please save the attribute first"
    When I save the attribute
    Then I should see the flash message "Attribute successfully created"
    When I check the "Automatic option sorting" switch
    And I create the following attribute options:
      | Code        |
      | small_size  |
      | medium_size |
      | large_size  |
    Then I should not see reorder handles
    And I should see "large_size medium_size small_size"
    When I uncheck the "Automatic option sorting" switch
    Then I should see reorder handles
    And I should see "small_size medium_size large_size"

  Scenario: Display attribute options ordered by code in PEF when no label on options
    Given I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    And I visit the "Values" tab
    Then I should see the "Options" section
    And I should see "To manage options, please save the attribute first"
    When I save the attribute
    Then I should see the flash message "Attribute successfully created"
    When I check the "Automatic option sorting" switch
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
    When I visit the "Values" tab
    Then I should see the "Options" section
    And I should see "To manage options, please save the attribute first"
    When I save the attribute
    Then I should see the flash message "Attribute successfully created"
    When I check the "Automatic option sorting" switch
    And I create the following attribute options:
      | Code        | en_US  | fr_FR  |
      | small_size  | Csmall | Apetit |
      | medium_size | Bmedium|        |
      | large_size  | Alarge | Cgrand |
    And I save the attribute
    Then I should not see the text "There are unsaved changes"
    When I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | a_product |
    And I press the "Save" button in the popin
    Then I should be on the product "a_product" edit page
    When I am on the "a_product" product page
    And I switch the locale to "en_US"
    And I add available attributes size
    Then I should see the ordered choices Alarge, Bmedium, Csmall in size
    When I switch the locale to "fr_FR"
    Then I should see the ordered choices [medium_size], Apetit, Cgrand in size
