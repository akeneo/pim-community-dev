@javascript
Feature: Classify many products and product models at once
  In order to easily classify products and product models
  As a product manager
  I need to associate many products to categories at once with a form

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  @critical
  Scenario: Add several products and product models to categories at once
    When I sort by "ID" value ascending
    Given I select rows aphrodite, 1111111171 and amor
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I press the "Master" button
    And I expand the "master" category
    And I click on the "master_men" category
    When I confirm mass edit
    And I wait for the "add_to_category" job to finish
    Then the categories of the product model "aphrodite" should be "master_men, master_women_blouses, print_clothing and supplier_zaro"
    Then the categories of the product "1111111114" should be "master_men, master_women_blouses, print_clothing and supplier_zaro"
    Then the categories of the product "1111111171" should be "master_accessories_bags, master_men, print_accessories and supplier_zaro"
    Then the categories of the product model "amor" should be "master_men, master_men_blazers and supplier_zaro"
