@javascript
Feature: Delete many product at once
  In order to easily manage products
  As a product manager
  I need to be able to remove many products at once

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      sku;family;categories;name-en_US;price;size
      boots_S36;boots;winter_collection;Amazing boots;20 EUR, 25 USD;36
      boots_S37;boots;winter_collection;Amazing boots;20 EUR, 25 USD;37
      boots_S38;boots;winter_collection;Amazing boots;20 EUR, 25 USD;38
      boots_S39;boots;winter_collection;Amazing boots;20 EUR, 25 USD;39
      boots_S40;boots;winter_collection;Amazing boots;20 EUR, 25 USD;40
      boots_S41;boots;winter_collection;Amazing boots;20 EUR, 25 USD;41
      sneakers_S39;sneakers;summer_collection;Sneakers;50 EUR, 60 USD;39
      sneakers_S40;sneakers;summer_collection;Sneakers;50 EUR, 60 USD;40
      sneakers_S41;sneakers;summer_collection;Sneakers;50 EUR, 60 USD;41
      sneakers_S42;sneakers;summer_collection;Sneakers;50 EUR, 60 USD;42
      sneakers_S43;sneakers;summer_collection;Sneakers;50 EUR, 60 USD;43
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I am on the products grid

  Scenario: Successfully remove many products
    Given I select rows boots_S36 and boots_S37
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products?"
    When I confirm the removal
    And I refresh current page
    Then I should not see products boots_S36 and boots_S37
    And the grid should contain 9 elements

  Scenario: Successfully "mass" delete one product
    Given I select row boots_S38
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products?"
    When I confirm the removal
    And I refresh current page
    Then I should not see product boots_S38
    And the grid should contain 10 elements

  Scenario: Successfully mass delete visible products
    Given I sort by "ID" value ascending
    And I select rows boots_S36
    And I select all visible entities
    Then I press the "Delete" button
    And I should see the text "Are you sure you want to delete the selected products?"
    When I confirm the removal
    And I refresh current page
    Then the grid should contain 0 elements

  Scenario: Successfully mass delete all products
    Given I select rows boots_S36
    And I select all entities
    Then I press the "Delete" button
    And I should see the text "Are you sure you want to delete the selected products?"
    When I confirm the removal
    And I refresh current page
    Then the grid should contain 0 elements

  @jira https://akeneo.atlassian.net/browse/PIM-3849
  Scenario: Successfully mass delete complete products on a different scope
    Given the following products:
      | sku       | family | categories        | name-en_US    | price          | size | color | lace_color  |
      | boots_S42 | boots  | winter_collection | Amazing boots | 20 EUR, 25 USD | 42   | red   | laces_black |
    And I launched the completeness calculator
    And I reload the page
    And I switch the scope to "Mobile"
    And I filter by "completeness" with operator "equals" and value "yes"
    And I select rows boots_S42
    When I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products?"
    When I confirm the removal
    Then the grid should contain 0 element
