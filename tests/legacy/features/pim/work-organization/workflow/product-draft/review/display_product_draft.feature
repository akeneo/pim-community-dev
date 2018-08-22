@javascript
Feature: Review a product draft
  In order to control which data should be applied to a product
  As a product manager
  I need to be able to review a product draft

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                   | jackets           |
      | categories               | winter_top        |
      | sku                      | my-jacket         |
      | name-en_US               | Jacket            |
      | description-en_US-mobile | An awesome jacket |
      | number_in_stock-mobile   | 4                 |
      | number_in_stock-tablet   | 20                |
      | price                    | 45 USD            |
      | manufacturer             | Volcom            |
      | weather_conditions       | dry, wet          |
      | handmade                 | 0                 |
      | release_date-mobile      | 2014-05-14        |
      | length                   | 60 CENTIMETER     |
      | legacy_attribute         | legacy            |
      | datasheet                |                   |
      | side_view                |                   |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully display the original value in the copy panel
    Given Mary proposed the following change to "my-jacket":
      | field | value       | tab                 |
      | SKU   | your-jacket | Product information |
    And I am logged in as "Mary"
    And I edit the "my-jacket" product
    And I open the comparison panel
    And I switch the comparison locale to "en_US"
    And I switch the comparison scope to "mobile"
    And I switch the comparison source to "working_copy"
    Then the SKU comparison value should be "my-jacket"

  Scenario: Successfully display the original value in the copy panel where there is no modifications
    Given Mary proposed the following change to "my-jacket":
      | field | value       | tab                 |
      | SKU   | your-jacket | Product information |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    And I fill in the following information:
      | SKU | your-jacket |
    Then I save the product
    When I visit the "Proposals" column tab
    Then I should see the text "The value \"your-jacket\" is the same between the proposal and the working copy."

  @jira https://akeneo.atlassian.net/browse/PIM-6935
  Scenario: Successfully got to a product from proposal grid
    Given Mary proposed the following change to "my-jacket":
      | field | value       | tab                 |
      | SKU   | your-jacket | Product information |
    And I am logged in as "Julia"
    And I am on the proposals page
    Then I should see the text "Jacket"
    And I follow the link "Jacket"
    Then I should see the text "Product information"
    And I should see the text "Display all attributes"
