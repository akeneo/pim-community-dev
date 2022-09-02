@javascript @published-product-feature-enabled
Feature: Display localizable and scopable attributes
  In order to be able to use localizable and scopable attributes of the published products
  As a product manager
  I need to see and manage the localizable and scopable attributes

  Background:
    Given a "apparel" catalog configuration
    And the following products:
      | sku          | family  | name-en_US     | name-de_DE          | customer_rating-ecommerce | customer_rating-print |
      | black_jacket | jackets | A black jacket | Eine schwarze Jacke | 1                         | 2                     |
    And I am logged in as "Julia"
    And I publish the product "black_jacket"

  @info https://akeneo.atlassian.net/browse/PIM-5949
  Scenario: Successfully change locale
    Given I show the "black_jacket" Published Product
    Then the field Name should contain "A black jacket"
    When I switch the locale to "de_DE"
    Then the field Name should contain "Eine schwarze Jacke"
