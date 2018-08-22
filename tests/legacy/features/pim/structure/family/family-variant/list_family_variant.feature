@javascript
Feature: list family variant
  In order to provide accurate information about a family
  As an administrator
  I need to be able to list family variant in a family

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit a family
    Given I am on the "Clothing" family page
    And I visit the "Variants" tab
    Then the grid should contain 5 elements
    And I should see the text "Clothing by material and size"
    And I should see the text "Color, Size"
    And I should see the text "Variant axis level 1"
    Then I search "Clothing by color"
    Then the grid should contain 3 elements
    And I should not see the text "Clothing by material and size"
    And I should see the text "Color, Size"

  @jira https://akeneo.atlassian.net/browse/PIM-7299
  Scenario: Successfully show pagination on family variant grid in family edit form
    Given the following family:
      | code                      | label-en_US   | attributes      |
      | family_with_many_variants | Many variants | sku,color,image |
    And the following family variants:
      | code       | family                    | variant-axes_1 | variant-attributes_1 |
      | variant_1  | family_with_many_variants | color          | sku,image            |
      | variant_2  | family_with_many_variants | color          | sku,image            |
      | variant_3  | family_with_many_variants | color          | sku,image            |
      | variant_4  | family_with_many_variants | color          | sku,image            |
      | variant_5  | family_with_many_variants | color          | sku,image            |
      | variant_6  | family_with_many_variants | color          | sku,image            |
      | variant_7  | family_with_many_variants | color          | sku,image            |
      | variant_8  | family_with_many_variants | color          | sku,image            |
      | variant_9  | family_with_many_variants | color          | sku,image            |
      | variant_10 | family_with_many_variants | color          | sku,image            |
      | variant_11 | family_with_many_variants | color          | sku,image            |
      | variant_12 | family_with_many_variants | color          | sku,image            |
      | variant_13 | family_with_many_variants | color          | sku,image            |
      | variant_14 | family_with_many_variants | color          | sku,image            |
      | variant_15 | family_with_many_variants | color          | sku,image            |
      | variant_16 | family_with_many_variants | color          | sku,image            |
      | variant_17 | family_with_many_variants | color          | sku,image            |
      | variant_18 | family_with_many_variants | color          | sku,image            |
      | variant_19 | family_with_many_variants | color          | sku,image            |
      | variant_20 | family_with_many_variants | color          | sku,image            |
      | variant_21 | family_with_many_variants | color          | sku,image            |
      | variant_22 | family_with_many_variants | color          | sku,image            |
      | variant_23 | family_with_many_variants | color          | sku,image            |
      | variant_24 | family_with_many_variants | color          | sku,image            |
      | variant_25 | family_with_many_variants | color          | sku,image            |
      | variant_26 | family_with_many_variants | color          | sku,image            |
      | variant_27 | family_with_many_variants | color          | sku,image            |
      | variant_28 | family_with_many_variants | color          | sku,image            |
      | variant_29 | family_with_many_variants | color          | sku,image            |
      | variant_30 | family_with_many_variants | color          | sku,image            |
    When I am on the "family_with_many_variants" family page
    And I visit the "Variants" tab
    Then the grid should contain 25 elements
    And the last page number should be 2
