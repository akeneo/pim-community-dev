@javascript @ce
Feature: Edit sequentially some products
  In order to enrich the catalog
  As a regular user
  I need to be able to edit sequentially some products

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Mary"
    And the following products:
      | sku          | family   |
      | blue_sandal  | sandals  |
      | black_sandal | sandals  |
      | white_sandal | sandals  |
      | boot         | boots    |
      | sneaker      | sneakers |
    And I am logged in as "Julia"
    And I am on the products page
    And I sort by "SKU" value ascending

  Scenario: Successfully sequentially edit some products
    Given I select rows white_sandal, boot and sneaker
    When I press sequential-edit button
    Then I should be on the product "boot" edit page
    Then I should see the text "Save and next"
