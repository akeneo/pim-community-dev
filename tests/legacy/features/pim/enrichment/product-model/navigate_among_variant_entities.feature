@javascript
Feature: Navigate among variant entities
  In order to be able to add and remove associations
  As a product manager
  I need to be able to navigate among variant entities on the product edit form

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Browse ascendant and descendant entities on 2 levels of variation
    When I edit the "apollon" product model
    Then the variant navigation selected axis values for level 0 should be "Common"
    And the variant navigation axis name for level 1 should be "Color"
    And the variant navigation axis name for level 2 should be "Size"
    When I open the variant navigation children selector for level 1
    Then I should see the "Blue" element in the variant children selector for level 1
    And I should see the "Red" element in the variant children selector for level 1
    When I filter the variant navigation children selector for level 1 with text "pi"
    Then I should see the "Pink" element in the variant children selector for level 1
    When I filter the variant navigation children selector for level 1 with text "pi"
    Then I should not see the "Blue" element in the variant children selector for level 1
    When I select the child "Red" for level 1
    Then I should be on the product model "apollon_red" edit page
    And the variant navigation selected axis values for level 0 should be "Common"
    And the variant navigation axis name for level 1 should be "Color"
    And the variant navigation axis name for level 2 should be "Size"
    And the variant navigation selected axis values for level 1 should be "Red"
    When I open the variant navigation children selector for level 2
    Then I should see the "XXL" element in the variant children selector for level 2
    And I should see the "M" element in the variant children selector for level 2
    When I filter the variant navigation children selector for level 2 with text "xx"
    Then I should see the "XXL" element in the variant children selector for level 2
    When I filter the variant navigation children selector for level 2 with text "xx"
    Then I should not see the "M" element in the variant children selector for level 2
    When I select the child "XXL" for level 2
    Then I should be on the product "1111111126" edit page
    And the variant navigation selected axis values for level 0 should be "Common"
    And the variant navigation axis name for level 1 should be "Color"
    And the variant navigation axis name for level 2 should be "Size"
    And the variant navigation selected axis values for level 1 should be "Red"
    And the variant navigation selected axis values for level 2 should be "XXL"
    When I navigate to the selected element for level 1
    Then I should be on the product model "apollon_red" edit page
    When I navigate to the selected element for level 0
    Then I should be on the product model "apollon" edit page

  @critical
  Scenario: Browse ascendant and descendant entities on 1 level of variation
    When I edit the "brooksblue" product model
    Then the variant navigation selected axis values for level 0 should be "Common"
    And the variant navigation axis name for level 1 should be "EU shoes size"
    When I open the variant navigation children selector for level 1
    Then I should see the "41" element in the variant children selector for level 1
    And I should see the "42" element in the variant children selector for level 1
    And I should see the "43" element in the variant children selector for level 1
    When I select the child "42" for level 1
    Then I should be on the product "1111111287" edit page
    And the variant navigation selected axis values for level 0 should be "Common"
    And the variant navigation axis name for level 1 should be "EU shoes size"
    And the variant navigation selected axis values for level 1 should be "42"
    And I should see the text "Brooks blue"
    When I navigate to the selected element for level 0
    Then I should be on the product model "brooksblue" edit page


  Scenario: Variant navigation bar has the context of the product edit form
    When I edit the "apollon_red" product model
    Then the variant navigation selected axis values for level 0 should be "Common"
    And the variant navigation axis name for level 1 should be "Color"
    And the variant navigation axis name for level 2 should be "Size"
    And the variant navigation selected axis values for level 1 should be "Red"
    When I open the variant navigation children selector for level 1
    Then I should see the "Red" element in the variant children selector for level 1
    And I should see the "Blue" element in the variant children selector for level 1
    Then completeness for element "XXL" in the variant children selector for level 1 should be "100%"
    And I switch the locale to "fr_FR"
    And the variant navigation axis name for level 1 should be "Couleur"
    And the variant navigation axis name for level 2 should be "Taille"
    And the variant navigation selected axis values for level 1 should be "Rouge"
    When I open the variant navigation children selector for level 1
    Then I should see the "Rouge" element in the variant children selector for level 1
    And I should see the "Bleu" element in the variant children selector for level 1
    And completeness for element "XXL" in the variant children selector for level 1 should be "85%"

  Scenario: If I can't edit an attribute that comes from a parent, I can click on a link to open the related entity
    When I am on the "1111111127" product page
    And I visit the "Marketing" group
    Then I should see the text "This attribute can be updated in the attributes by Color"
    When I click on "This attribute can be updated in the attributes by Color" footer message of the field "Variation Name"
    Then I should be on the product model "apollon_red" edit page
    When I visit the "ERP" group
    And I click on "This attribute can be updated in the common attributes." footer message of the field "Supplier"
    Then I should be on the product model "apollon" edit page
