@javascript
Feature: Display a completeness dropdown on products
  In order to quickly have information about product completeness
  As a product manager
  I need to be able to display a dropdown of the current product on the PEF

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: The dropdown displays completeness information for the product
    Given I am on the "1111111171" product page
    Then I should see the text "Complete: 60%"
    When I open the completeness dropdown
    And I should see the completeness in the dropdown:
      | locale | state   | missing_values | ratio | missing_required_attributes            |
      | de_DE  | warning | 3              | 40%   | Model name, Variation Name, Collection |
      | en_US  | warning | 2              | 60%   | Variation Name, Collection             |
      | fr_FR  | warning | 3              | 40%   | Model name, Variation Name, Collection |

  Scenario: The dropdown displays completeness information for the variant product
    Given I am on the "1111111112" product page
    Then I should see the text "Complete: 54%"
    When I open the completeness dropdown
    And I should see the completeness in the dropdown:
      | locale | state   | missing_values | ratio | missing_required_attributes                                                                   |
      | de_DE  | warning | 6              | 45%   | Model description, Model picture, Variation picture, Composition, Care instructions, Material |
      | en_US  | warning | 5              | 54%   | Model picture, Variation picture, Composition, Care instructions, Material                    |
      | fr_FR  | warning | 6              | 45%   | Model description, Model picture, Variation picture, Composition, Care instructions, Material |

  Scenario: I can click on missing required attributes to focus them
    Given I am on the "1111111171" product page
    Then I should see the text "Complete: 60%"
    When I open the completeness dropdown
    And I click on the missing "name" value for "de_DE" locale
    Then the locale "de_DE" should be selected

  Scenario: If a missing required attribute is on an parent entity, I'm redirected to this entity
    Given I am on the "1111111119" product page
    Then I should see the text "Complete: 63%"
    When I open the completeness dropdown
    And I click on the missing "variation_image" value for "en_US" locale
    Then I should be on the product model "apollon_blue" edit page
    When I am on the "1111111119" product page
    Then I should see the text "Complete: 63%"
    When I open the completeness dropdown
    And I click on the missing "care_instructions" value for "en_US" locale
    Then I should be on the product model "apollon" edit page

  Scenario: Display missing required attributes on products and product models
    Given I am on the "apollon_blue" product model page
    Then I should see the text "4 missing required attributes"
    When I am on the "apollon" product model page
    Then I should see the text "1 missing required attribute"
    Given I am on the "1111111119" product page
    Then I should see the text "4 missing required attributes"
    And I should see the text "ERP name"
    When I click on the missing required attributes overview link
    Then I should not see the text "ERP name"
    But I should see the text "Care instructions"
