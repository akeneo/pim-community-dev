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
      | handmade                  | 0                |
      | release_date-mobile       | 2014-05-14        |
      | length                    | 60 CENTIMETER     |
      | legacy_attribute          | legacy            |
      | datasheet                 |                   |
      | side_view                 |                   |

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept an identifier attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field | value       |
      | SKU   | your-jacket |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "SKU"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    But the field SKU should contain "your-jacket"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a text attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Name"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Coat"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a textarea attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field              | value           |
      | mobile Description | An awesome coat |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Description"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product mobile Description should be "An awesome coat"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a number attribute from a product draft
    Given Mary proposed the following scopable change to "my-jacket":
      | tab       | field                     | value |
      | Marketing | ecommerce Number in stock | 20    |
      | Marketing | mobile Number in stock    | 40    |
      | Marketing | print Number in stock     | 50    |
      | Marketing | tablet Number in stock    | 200   |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Number in stock"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I expand the "Number in stock" attribute
    Then the product ecommerce Number in stock should be "20"
    Then the product mobile Number in stock should be "40"
    And the product print Number in stock should be "50"
    And the product tablet Number in stock should be "200"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a prices attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab       | field   | value |
      | Marketing | $ Price | 90    |
      | Marketing | € Price | 150   |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Price"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Marketing" group
    Then the product Price in $ should be "90.00"
    Then the product Price in € should be "150.00"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a simpleselect attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field        | value |
      | Manufacturer | Nike  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Manufacturer"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Manufacturer should be "Nike"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a multiselect attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field              | value               |
      | Weather conditions | Hot, Cold, Dry, Wet |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Weather conditions"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Weather conditions should be "Cold, Dry, Hot and Wet"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a file attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab   | field     | value            |
      | Media | Datasheet | file(akeneo.txt) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Datasheet"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Media" group
    Then I should see "akeneo.txt"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept an image attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | tab   | field     | value            |
      | Media | Side view | file(akeneo.jpg) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Side view"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Media" group
    Then I should see "akeneo.jpg"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a boolean attribute from a product draft
    Given Mary proposed the following change to "my-jacket":
      | field    | value      |
      | Handmade | state(yes) |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Handmade"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Handmade should be "1"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a date attribute from a product draft
    Given Mary proposed the following scopable change to "my-jacket":
      | field                  | value      |
      | ecommerce Release date | 2014-05-20 |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "ecommerce - Release date"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product ecommerce Release date should be "2014-05-20"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept a metric attribute from a product draft
    Given Mary proposed the following scopable change to "my-jacket":
      | tab   | field  | value |
      | Sizes | Length | 40    |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Length"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Sizes" group
    Then the product Length should be "40"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully refuse a waiting for approval product draft
    Given Mary proposed the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "refuse" action of the row which contains "Name"
    Then the grid should contain 1 element
    And the row "Mary" should contain:
      | column | value       |
      | Status | In progress |
    When I visit the "Attributes" tab
    Then the product Name should be "Jacket"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully remove an in progress product draft
    Given Mary started to propose the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "remove" action of the row which contains "Name"
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    Then the product Name should be "Jacket"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Not being able to approve or refuse a proposal with values I can't edit
    Given Mary proposed the following change to "my-jacket":
      | field                          | value                                | tab                        |
      | Old attribute not used anymore | a new value for the legacy attribute | old group not used anymore |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    Then I should not be able to view the "Approve" action of the row which contains "Old attribute not used anymore"
    And I should not be able to view the "Refuse" action of the row which contains "Old attribute not used anymore"
    And I should see "Can't be reviewed"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Not being able to delete a draft with values I can't edit
    Given Mary started to propose the following change to "my-jacket":
      | field                          | value                                | tab                        |
      | Old attribute not used anymore | a new value for the legacy attribute | old group not used anymore |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    Then I should not be able to view the "Delete" action of the row which contains "Old attribute not used anymore"
    And I should see "Can't be deleted"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully display the original value in the copy panel
    Given Mary proposed the following change to "my-jacket":
      | field | value       |
      | SKU   | your-jacket |
    And I am logged in as "Mary"
    And I edit the "my-jacket" product
    Then I display the tooltip for the "SKU" attribute modified
    Then I should see "my-jacket" in the popover
