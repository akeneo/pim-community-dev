@javascript
Feature: Apply defined asset category permissions on asset grid row actions
  In order to know when I have the rights to do some actions
  As Pamela
  I want to see asset grid row actions only when I have the rights to execute them

  Background:
    Given a "clothing" catalog configuration
    And the following assets:
      | code          | description             | enabled | categories          |
      | logo          |                         | yes     | images              |
      | other_image   |                         | yes     |                     |
      | technical_doc | technical documentation | yes     | technical_documents |

  Scenario: Display the asset classification action only if the user owns the asset
    Given I am logged in as "Pamela"
    And I am on the assets grid
    And I change the page size to 25
    Then the grid should contain 17 elements
    And I should see assets logo and other_image
    And I should be able to view the "Classify the asset" action of the row which contains "other_image"
    But I should not see assets technical_doc
