@javascript
Feature: Edit a product model
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product model

  Background:
    Given a "catalog_modeling" catalog configuration

  Scenario: Successfully display family variant name of a product model
    Given I am logged in as "Mary"
    And I edit the "amor" product model
    Then I should see the text "Clothing by color/size"

  @critical
  Scenario: Successfully edit and save a root product model
    Given I am logged in as "Mary"
    And I edit the "amor" product model
    And I visit the "Marketing" group
    And I fill in the following information:
      | Model name | Heritage jacket navy chilly tiki |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the product Model name should be "Heritage jacket navy chilly tiki"

  @critical
  Scenario: Successfully edit and save a sub product model
    Given I am logged in as "Mary"
    And I edit the "apollon_blue" product model
    And I visit the "Marketing" group
    And I fill in the following information:
      | Variation Name | Apollonito blue |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the product Variation Name should be "Apollonito blue"

  @critical
  Scenario: Parent attributes of a sub product model are read only
    Given I am logged in as "Mary"
    And I edit the "apollon_blue" product model
    When I visit the "Marketing" group
    Then the field Model name should be read only
    And the field Model description should be read only
    When I visit the "Medias" group
    Then the field Notice should be read only
    And I should see the text "This attribute can be updated in the common attributes."

  @critical
  Scenario: Variant axes attributes are read only
    Given I am logged in as "Mary"
    And I edit the "apollon_blue" product model
    And I visit the "Product" group
    Then the field Color (variant axis) should be read only
    And I should see the text "Color (Variant axis)"

  @jira https://akeneo.atlassian.net/browse/PIM-6861
  Scenario: Display a product model without any children
    Given I am logged in as "Mary"
    When I am on the "1111111113" product page
    And I press the secondary action "Delete"
    Then I should see the text "Confirm deletion"
    And I confirm the removal
    When I am on the "1111111112" product page
    And I press the secondary action "Delete"
    Then I should see the text "Confirm deletion"
    And I confirm the removal
    When I am on the "1111111111" product page
    And I press the secondary action "Delete"
    Then I should see the text "Confirm deletion"
    And I confirm the removal
    When I edit the "amor" product model
    And I visit the "Marketing" group
    Then the product Model name should be "Heritage jacket navy"

  @jira https://akeneo.atlassian.net/browse/PIM-6816
  Scenario: Successfully display a validation error message
    Given I am logged in as "Mary"
    And I am on the "amor" product model page
    And I visit the "ERP" group
    And I change the Price to "foobar USD"
    When I press the "Save" button
    Then I should see validation tooltip "This value should be a valid number."
    And there should be 1 error in the "ERP" tab

  Scenario: Quickly view missing values to fill in
    Given I am logged in as "Mary"
    When I am on the "amor" product model page
    Then the Care instructions, Material, Model picture fields should be highlighted
    When I visit the "Marketing" group
    And I fill in the following information:
      | Collection |        |
    And I visit the "Product" group
    And I fill in the following information:
      | Material   | cotton |
    And I press the "Save" button
    And I visit the "All" group
    Then the Care instructions, Collection, Model picture fields should be highlighted

  @jira https://akeneo.atlassian.net/browse/PIM-7382
  Scenario: Successfully display a product model's scopable value after editing it 
    Given I am logged in as "Mary"
    And I am on the "bacchus" product model page
    And I fill in the following information:
      | Model description | Another great description |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the field Model description should contain "Another great description"
