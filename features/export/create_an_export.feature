Feature: Create an export
  In order to use my PIM data into my front applications
  As a user
  I need to be able to create export jobs

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  @javascript
  Scenario: Successfully create a product export into csv
    Given I am on the exports page
    And I create a new "Product export in CSV" export
    When I fill in the following information:
      | Code  | mobile_product_export |
      | Label | Mobile product export |
    And I visit the "Configuration" tab
    And I fill in the following information:
        | File path | /tmp/export.csv |
    And I select the channel "Mobile"
    And I check "With header"
    And I save the export
    Then I should see "Export profile - Mobile product export"
    And I should see flash message "The export has been successfully created."
    Then I press the "Edit" button
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | action | version | property | value                 |
      | create | 1       | code     | mobile_product_export |

  Scenario: Fail to create an unknown product export
    Given I try to create an unknown export
    Then I should be redirected on the export index page
    And I should see "Failed to create an export with an unknown job definition."
