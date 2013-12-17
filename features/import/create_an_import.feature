@javascript
Feature: Create an import
  In order to use my PIM data into my front applications
  As a user
  I need to be able to create import jobs

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the imports page
    
  Scenario: Successfully create an import
    Given I create a new import
    And I should see the Code, Label and Job fields
    When I fill in the following information in the popin:
      | Code  | PRODUCT_IMPORT        |
      | Label | Products import       |
      | Job   | Product import in CSV |
    And I press the "Save" button
    Then I should see "Edit import profile - Products import"
