@javascript
Feature: Mass edit jobs
  In order to easily make a mass edit
  As a product manager
  I need to be able to see the result of a mass edit jobs and to download logs

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  @ce
  Scenario: Go to the job execution page for a "mass edit" (by clicking on the notifications) and then check buttons status on the header
    Given a disabled "boat" product
    And a disabled "jet-ski" product
    And I am on the products grid
    When I select rows boat and jet-ski
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Change status (enable / disable)" operation
    And I enable the products
    And I wait for the "update_product_value" job to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                 |
      | success | Mass edit Mass update products finished |
    When I go on the last executed job resume of "update_product_value"
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - Mass update products [update_product_value]"
    And I should see the secondary action "Download log"
    And I should not see the text "Download read files"
    And I should not see the text "Download generated file"
    And I should not see the text "Download generated archive"
    And I should not see the text "Show profile"
