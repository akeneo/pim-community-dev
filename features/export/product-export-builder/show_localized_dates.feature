@javascript
Feature: Show localized date in export builder
  In order to have localized UI
  As a product manager
  I need to be able to show localized dates in the export builder

  Scenario: Show localized date for an attribute date
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Destocking date
    And I filter by "destocking_date" with operator "Lower than" and value "08/13/2016"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I press the "Edit" button
    When I visit the "Content" tab
    Then I should see the text "Lower than"
    And the field filter-value-start should contain "08/13/2016"
    When I logout
    And I am logged in as "Julien"
    And I am on the "csv_footwear_product_export" export job page
    And I press the "Modifier" button
    And I visit the "Contenu" tab
    Then I should see the text "Inférieur à"
    And the field filter-value-start should contain "13/08/2016"

  @skip-nav
  Scenario: Show localized date for updated since field
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "updated" with operator "Updated products since this date" and value "08/13/2016"
    Then I should see the text "There are unsaved changes"
    And I press the "Save" button
    When I visit the "Content" tab
    Then I should see the text "Updated products since this date"
    And the field filter-value-updated should contain "08/13/2016"
    When I logout
    And I am logged in as "Julien"
    And I am on the "csv_footwear_product_export" export job page
    And I visit the "Contenu" tab
    Then I should see the text "Produits mis à jours depuis cette date"
    And the field filter-value-updated should contain "13/08/2016"
