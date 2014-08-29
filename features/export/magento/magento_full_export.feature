@javascript
Feature: Magento full export
  In order to export products to Magento
  As an Administrator
  I need to be able to export the architecture to Magento

  Scenario: Successfully export architecture to Magento
    Given  a "magento" catalog configuration
    And I launched the completeness calculator
    And I am logged in as "peter"
    When I am on the "magento_full_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I fill in the "category" mapping:
      | Master catalog (default) | Default Category |
    And I press the "Save" button and I wait "15"s
    Then I launch the export job
    And I wait for the "magento_full_export" job to finish
