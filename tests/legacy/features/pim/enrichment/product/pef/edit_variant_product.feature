@javascript
Feature: Edit a variant product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a variant product

  Background:
    Given a "catalog_modeling" catalog configuration

  Scenario: Successfully display family variant name of a product model
    Given I am logged in as "Mary"
    And I edit the "1111111119" product
    Then I should see the text "Clothing by color and size"

  @critical
  Scenario: Successfully edit and save a variant product
    Given I am logged in as "Mary"
    And I edit the "1111111119" product
    And I visit the "Product" group
    And I fill in the following information:
      | Weight | 8000 Gram |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the product Weight should be "8000 Gram"

  @critical
  Scenario: Attributes coming from parent are read only
    Given I am logged in as "Mary"
    And I edit the "1111111119" product
    When I visit the "Product" group
    Then the field Composition should be read only
    And the field Material should be read only
    And I should see the text "This attribute can be updated in the attributes by Color"
    And I should see the text "Color (Variant axis)"
    And I should see the text "Size (Variant axis)"

  @critical
  Scenario: Attributes coming from common attributes are read only
    Given I am logged in as "Mary"
    And I edit the "1111111119" product
    When I visit the "Ecommerce" group
    Then the field Meta title should be read only
    And the field Meta description should be read only
    And I should see the text "This attribute can be updated in the common attributes."

  @critical
  Scenario: Axis attributes are read only
    Given I am logged in as "Mary"
    And I edit the "1111111119" product
    And I visit the "Product" group
    Then the field Size (variant axis) should be read only
    And I should see the text "Size (variant axis)"
