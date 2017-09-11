@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "apparel" catalog configuration
    And the following products:
      | sku          | family  | name-en_US     | name-de_DE          | customer_rating-ecommerce | customer_rating-print |
      | black_jacket | jackets | A black jacket | Eine schwarze Jacke | 1                         | 2                     |
      | white_jacket | jackets | A white jacket | Ein weißer Jacke    | 3                         | 4                     |
    And I am logged in as "Julia"
    And I am on the products grid

  @info https://akeneo.atlassian.net/browse/PIM-5351
  Scenario: Successfully mass edit scoped product values
    Given I switch the scope to "Print"
    And I select rows black_jacket and white_jacket
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    Then I should see the text "The selected product's attributes will be edited with the following data for the locale English (United States) and the channel Print, chosen in the products grid."
    When I display the Customer rating attribute
    And I change the "Customer rating" to "5"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the unlocalized ecommerce customer_rating of "black_jacket" should be "[1]"
    And the unlocalized ecommerce customer_rating of "white_jacket" should be "[3]"
    And the unlocalized print customer_rating of "black_jacket" should be "[5]"
    And the unlocalized print customer_rating of "white_jacket" should be "[5]"

  @info https://akeneo.atlassian.net/browse/PIM-5351
  Scenario: Successfully mass edit localized product values
    Given I switch the locale to "de_DE"
    When I switch the scope to "Ecommerce"
    And I select rows black_jacket and white_jacket
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    Then I should see the text "The selected product's attributes will be edited with the following data for the locale Deutsch (Deutschland) and the channel Ecommerce, chosen in the products grid."
    When I display the Name attribute
    And I change the "Name" to "Une veste"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the german localizable value name of "black_jacket" should be "Une veste"
    And the german localizable value name of "white_jacket" should be "Une veste"
    And the english localizable value name of "black_jacket" should be "A black jacket"
    And the english localizable value name of "white_jacket" should be "A white jacket"
