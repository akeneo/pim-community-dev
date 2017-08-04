@javascript
Feature: Save only filled fields after a save
  In order to avoid to store empty product values
  As a product manager
  I need to be save only filled fields

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku            | family   |
      | summer-sneaker | sneakers |
    And I am logged in as "Julia"

  Scenario: Successfully save only filled fields on PEF
    Given I am on the "summer-sneaker" product page
    And I fill in the following information:
      | Description | Sneakers are perfect for summer ! |
    And I visit the "Marketing" group
    And I fill in the following information:
      | Price  | 10 EUR  |
      | Rating | 2 stars |
    And I save the product
    Then the product "summer-sneaker" should have the following values:
      | description-en_US-tablet | Sneakers are perfect for summer ! |
      | price                    | 10.00 EUR                         |
      | rating                   | [2]                               |
    But the product "summer-sneaker" should not have the following values:
      | description-en_US-mobile |
      | name-en_US               |
      | weather_conditions       |
      | manufacturer             |
      | size                     |
      | side_view                |
      | top_view                 |
      | color                    |
      | lace_color               |
    But I should see the Price and Rating fields
    When I visit the "Product information" group
    Then I should see the Name, Description, Manufacturer fields
