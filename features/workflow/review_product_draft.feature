@javascript
Feature: Review a product draft
  In order to control which data should be applied to a product
  As a product manager
  I need to be able to review a product draft

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                    | jackets           |
      | categories                | winter_top        |
      | sku                       | my-jacket         |
      | name-en_US                | Jacket            |
      | description-en_US-mobile  | An awesome jacket |
      | number_in_stock-mobile    | 4                 |
      | number_in_stock-tablet    | 20                |
      | price                     | 45 USD            |
      | manufacturer              | Volcom            |
      | weather_conditions        | dry, wet          |
      | handmade                  | 0                 |
      | release_date-mobile       | 2014-05-14        |
      | length                    | 60 CENTIMETER     |
      | legacy_attribute          | legacy            |
      | datasheet                 |                   |
      | side_view                 |                   |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept an identifier attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field | value       |
      | SKU   | your-jacket |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "SKU"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product SKU should be "your-jacket"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a text attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Name"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Coat"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a textarea attribute from a product draft
    Given the following product drafts:
      | product   | author | result                                                                                    | status |
      | my-jacket | Mary   | {"values":{"description":[{"locale":"en_US","scope":"mobile","data":"An awesome coat"}]}} | ready  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Description"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Description for scope "mobile" should be "An awesome coat"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a number attribute from a product draft
    Given the following product drafts:
      | product   | author | result                                                                                                                      | status |
      | my-jacket | Mary   | {"values":{"number_in_stock":[{"locale":null,"scope":"mobile","data":"40"},{"locale":null,"scope":"tablet","data":"200"}]}} | ready  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Number in stock"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    Then the product Number in stock for scope "mobile" should be "40"
    Then the product Number in stock for scope "tablet" should be "200"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a prices attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab       | field | value   |
      | Marketing | Price | 90 USD  |
      | Marketing | Price | 150 EUR |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Price"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    Then the product Price in USD should be "90.00"
    Then the product Price in EUR should be "150.00"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a simpleselect attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field        | value |
      | Manufacturer | Nike  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Manufacturer"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Manufacturer should be "Nike"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a multiselect attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field              | value               |
      | Weather conditions | Hot, Cold, Dry, Wet |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Weather conditions"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Weather conditions should be "cold, dry, hot and wet"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a file attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab   | field     | value            |
      | Media | Datasheet | file(akeneo.txt) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Datasheet"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Media" group
    Then I should see "akeneo.txt"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept an image attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab   | field     | value            |
      | Media | Side view | file(akeneo.jpg) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Side view"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Media" group
    Then I should see "akeneo.jpg"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a boolean attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field    | value      |
      | Handmade | state(yes) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Handmade"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Handmade should be "on"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a date attribute from a product draft
    Given the following product drafts:
      | product   | author | result                                                                             | status |
      | my-jacket | Mary   | {"values":{"release_date":[{"locale":null,"scope":"mobile","data":"2014-05-20"}]}} | ready  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Release date"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Release date for scope "mobile" should be "2014-05-20"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a metric attribute from a product draft
    Given the following product drafts:
      | product   | author | result                                                                                        | status |
      | my-jacket | Mary   | {"values":{"length":[{"locale":null,"scope":null,"data":{"data":"40","unit":"CENTIMETER"}}]}} | ready  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Length"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Sizes" group
    Then the product Length should be "40 CENTIMETER"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully reject a waiting for approval product draft
    Given Mary proposed the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "reject" action of the row which contains "Name"
    And I press the "Send" button in the popin
    Then the grid should contain 1 element
    And the row "Mary" should contain:
      | column | value       |
      | Status | In progress |
    When I visit the "Attributes" tab
    Then the product Name should be "Jacket"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully remove an in progress product draft
    Given Mary started to propose the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "remove" action of the row which contains "Name"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Jacket"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Not being able to approve or reject a proposal with values I can't edit
    Given Mary proposed the following change to "my-jacket":
      | field                          | value                                | tab                        |
      | Old attribute not used anymore | a new value for the legacy attribute | old group not used anymore |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    Then I should not be able to view the "Approve" action of the row which contains "Old attribute not used anymore"
    And I should not be able to view the "Reject" action of the row which contains "Old attribute not used anymore"
    And I should see "Can't be reviewed"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Not being able to delete a draft with values I can't edit
    Given Mary started to propose the following change to "my-jacket":
      | field                          | value                                | tab                        |
      | Old attribute not used anymore | a new value for the legacy attribute | old group not used anymore |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    Then I should not be able to view the "Delete" action of the row which contains "Old attribute not used anymore"
    And I should see "Can't be deleted"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully display the original value in the copy panel
    Given the following product drafts:
      | product   | author | result                                                                 | status |
      | my-jacket | Mary   | {"values":{"sku":[{"locale":null,"scope":null,"data":"your-jacket"}]}} | ready  |
    And I am logged in as "Mary"
    And I edit the "my-jacket" product
    Then the SKU original value for scope "mobile" and locale "en_US" should be "my-jacket"

  Scenario: Successfully be notified when someone sends a proposal for approval
    Given Mary proposed the following change to "my-jacket":
      | field | value       |
      | SKU   | your-jacket |
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type | message                                                         |
      | add  | Mary Smith has sent a proposal to review for the product Jacket |
    When I click on the notification "Mary Smith has sent a proposal to review for the product Jacket"
    Then I should be on the product "my-jacket" edit page
    And I should see the columns Author, Changes, Proposed at and Status
    And the grid should contain 1 element

  Scenario: Successfully be notified when someone sends a proposal for approval with a comment
    Given Mary proposed the following change to "my-jacket" with the comment "Please approve this fast.":
      | field | value       |
      | SKU   | your-jacket |
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type | message                                                         | comment                   |
      | add  | Mary Smith has sent a proposal to review for the product Jacket | Please approve this fast. |
    When I click on the notification "Mary Smith has sent a proposal to review for the product Jacket"
    Then I should be on the product "my-jacket" edit page
    And I should see the columns Author, Changes, Proposed at and Status
    And the grid should contain 1 element
