@deprecated @javascript
Feature: Execute a job
  In order to launch an import
  As a product manager
  I need to be able to execute a valid export

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Fail to see the import button of a job with validation errors
    When I am on the "footwear_product_import" import job page
    Then I should not see the "Import now" link

