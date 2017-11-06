@javascript
Feature: Remove attribute from a family
  In order to correct myself when I have wrongly added an attribute into a family
  As an administrator
  I need to be able to remove an attribute from a family

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully remove an attribute from a family and display it as removable from product
    Given I am on the "accessories" family page
    And I visit the "Attributes" tab
    When I remove the "material" attribute
    And I save the family
    And I should not see the text "There are unsaved changes."
    Then I should see the flash message "Attribute successfully removed from the family"
    When I am on the "1111111292" product page
    Then I should not see the text "Material"

  Scenario: Successfully remove an attribute from a family and it does not appear in the variant product edit page
    Given I am on the "accessories" family page
    And I visit the "Attributes" tab
    When I remove the "material" attribute
    And I save the family
    And I should not see the text "There are unsaved changes."
    Then I should see the flash message "Attribute successfully removed from the family"
    When I am on the "model-braided-hat" product model page
    Then I should not see the text "Material"

  Scenario: Impossible to remove some attributes from a family (used as label, used as image, used as axis)
    Given I am on the "shoes" family page
    And I visit the "Attributes" tab
    And I scroll down
    When I remove the "variation_name" attribute
    Then I should see the flash message "Cannot remove attribute used as label"
    When I remove the "variation_image" attribute
    Then I should see the flash message "Cannot remove used as the main picture"
    When I remove the "size" attribute
    Then I should see the flash message "Cannot remove this attribute used as a variant axis in a family variant"
    And I should see the text "size"

  Scenario: Sucessfully remove an attribute from a family removes it from the family variants.
    Given I am on the "shoes" family page
    And I visit the "Variants" tab
    When I click on the "Shoes by size and color" row
    Then I should see the text "EU Shoes Size"
    Then I should see the text "Weight"
    And I should see the text "Variation picture"
    And I should see the text "Model picture"
    When I press the cancel button in the popin
    And I visit the "Attributes" tab
    And I remove the "weight" attribute
    And I remove the "image" attribute
    And I save the family
    And I visit the "Variants" tab
    And I click on the "Shoes by size and color" row
    Then I should see the text "EU Shoes Size"
    And I should not see the text "Weight"
    And I should see the text "Variation picture"
    And I should not see the text "Model picture"

  @skip
  Scenario: Successfully update product completeness when removing a required attribute from a family
    Given I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I switch the attribute "manufacturer" requirement in channel "ecommerce"
    And I switch the attribute "manufacturer" requirement in channel "mobile"
    And I save the family
    Then I should not see the text "There are unsaved changes."
    When I launched the completeness calculator
    And I am on the "bag-noname" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel    | locale                  | state    | message         | ratio |
      | e-commerce | English (United States) | warning  | 1 missing value | 50%   |
      | e-commerce | French (France)         | warning  | 1 missing value | 50%   |
      | mobile     | English (United States) | disabled | none            | none  |
      | mobile     | French (France)         | warning  | 1 missing value | 50%   |
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I remove the "manufacturer" attribute
    And I save the family
    Then I should not see the text "There are unsaved changes."
    When I am on the "bag-noname" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel    | locale                  | state    | message            | ratio |
      | e-commerce | English (United States) |          | Not yet calculated |       |
      | e-commerce | French (France)         |          | Not yet calculated |       |
      | mobile     | English (United States) | disabled | none               | none  |
      | mobile     | French (France)         |          | Not yet calculated |       |
    When I launched the completeness calculator
    And I am on the "bag-noname" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel    | locale                  | state    | message  | ratio |
      | e-commerce | English (United States) | success  | Complete | 100%  |
      | e-commerce | French (France)         | success  | Complete | 100%  |
      | mobile     | English (United States) | disabled | none     | none  |
      | mobile     | French (France)         | success  | Complete | 100%  |
