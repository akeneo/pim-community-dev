@javascript
Feature: Edit sequentially some products
  In order to enrich the catalog
  As a regular user
  I need to be able to edit sequentially some products

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku          | family   |
      | blue_sandal  | sandals  |
      | black_sandal | sandals  |
      | white_sandal | sandals  |
      | boot         | boots    |
      | sneaker      | sneakers |
    And I am logged in as "Julia"
    And I am on the products grid
    And I switch the locale to "en_US"

  Scenario: Successfully sequentially edit some products
    Given I sort by "ID" value ascending
    And I select rows white_sandal, boot and sneaker
    When I press the "Sequential edit" button
    Then I should be on the product "boot" edit page
    Then I should see the text "Save and next"
    When I fill in the following information:
      | Name | Fur boots |
    And I press the "Save and next" button
    Then I should not see the text "There are unsaved changes."
    And I should be on the product "sneaker" edit page
    When I fill in the following information:
      | Name | Ultra sneaker |
    And I press the "Save and next" button
    Then I should not see the text "There are unsaved changes."
    And I should be on the product "white_sandal" edit page
    And the product "boot" should have the following values:
      | name-en_US | Fur boots |
    And the product "sneaker" should have the following values:
      | name-en_US | Ultra sneaker |

    @jira https://akeneo.atlassian.net/browse/PIM-4647
    Scenario: Successfully show product edit progression
      Given I sort by "ID" value ascending
      And I select rows white_sandal, boot and sneaker
      When I press the "Sequential edit" button
      Then I should be on the product "boot" edit page
      And I should see the text "1 / 3 products"
      When I am on the products grid
      And I select rows white_sandal, blue_sandal, boot and sneaker
      And I press the "Sequential edit" button
      Then I should be on the product "blue_sandal" edit page
      And I should see the text "1 / 4 products"
      When I fill in the following information:
        | Name | A new name |
      And I press the "Save and next" button
      Then I should be on the product "boot" edit page
      And I should see the text "2 / 4 products"

    @jira https://akeneo.atlassian.net/browse/PIM-4672
    Scenario: Keep product grid sorting order in sequential edit
      Given I sort by "Family" value ascending
      And I select rows sneaker, white_sandal
      When I press the "Sequential edit" button
      Then I should be on the product "white_sandal" edit page
