@javascript
Feature: Display the completeness of a product
  In order to see the completeness of a product in the catalog
  As a product manager
  I need to be able to display the completeness of a product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And the following products:
      | sku              | family   | manufacturer | weather_conditions | color | name-en_US | name-fr_FR  | price          | rating | size | lace_color  |
      | sneakers         | sneakers | Converse     | hot                | blue  | Sneakers   | Espadrilles | 69 EUR, 99 USD | 4      | 43   | laces_white |
      | sandals          | sandals  |              |                    | white |            | Sandales    |                |        |      |             |
      | my_nice_sneakers |          |              |                    |       |            |             |                |        |      |             |
    And the following product values:
      | product  | attribute   | value                 | locale | scope  |
      | sneakers | description | Great sneakers        | en_US  | mobile |
      | sneakers | description | Really great sneakers | en_US  | tablet |
      | sneakers | description | Grandes espadrilles   | fr_FR  | mobile |
      | sandals  | description | Super sandales        | fr_FR  | tablet |
      | sandals  | description | Super sandales        | fr_FR  | mobile |
    And I am logged in as "Julia"
    And I launched the completeness calculator

  Scenario: Successfully display the completeness of the products
    Given I am on the "sneakers" product page
    When I visit the "Completeness" column tab
    Then I should see the "tablet" completeness in position 1
    And I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | warning | 1              | 88%   |
      | tablet  | fr_FR  | warning | 2              | 77%   |
      | mobile  | en_US  | success | 0              | 100%  |
      | mobile  | fr_FR  | success | 0              | 100%  |
    When I am on the products grid
    Then I am on the "sandals" product page
    And I visit the "Attributes" column tab
    And the Name, Description fields should be highlighted
    And the Manufacturer, SKU fields should not be highlighted
    And the Product information, Marketing, Sizes, Media groups should be highlighted
    And the Colors group should not be highlighted
    And I visit the "Completeness" column tab
    And I visit the "Attributes" column tab
    When I switch the locale to "fr_FR"
    And I visit the "Completeness" column tab
    And I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | en_US  | warning | 6              | 25%   |
      | tablet  | fr_FR  | warning | 4              | 50%   |
      | mobile  | en_US  | warning | 3              | 40%   |
      | mobile  | fr_FR  | warning | 2              | 60%   |

  Scenario: Successfully display the completeness of the products in the grid
    Given I am on the products grid
    When I switch the locale to "en_US"
    And I switch the scope to "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 40%   |
    When I switch the scope to "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 88%   |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 25%   |
    When I switch the locale to "fr_FR"
    And I switch the scope to "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 60%   |
    When I switch the scope to "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 77%   |
    And the row "sandals" should contain:
     | column   | value |
     | complete | 50%   |

  Scenario: Successfully display the completeness of the product in the grid after product save (PIM-2916)
    Given I am on the "sneakers" product page
    And I visit the "Attributes" column tab
    And I visit the "Media" group
    And I attach file "SNKRS-1C-s.png" to "Side view"
    And I save the product
    And I am on the products grid
    And I switch the locale to "en_US"
    When I switch the scope to "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    When I switch the scope to "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    And I switch the locale to "fr_FR"
    When I switch the scope to "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    When I switch the scope to "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 88%   |

  Scenario: Don't display the completeness if the family is not defined
    Given I am on the "sneakers" product page
    When I visit the "Completeness" column tab
    Then I change the family of the product to ""
    And I should see the text "No family defined. Please define a family to calculate the completeness of this product."
    When I change the family of the product to "Sneakers"
    Then I should not see the text "No family defined. Please define a family to calculate the completeness of this product."
    When I change the family of the product to "Boots"
    Then I should see the text "You just changed the family of the product. Please save it first to calculate the completeness for the new family."

  @jira https://akeneo.atlassian.net/browse/PIM-4489
  Scenario: Don't display the completeness if the family is not defined on product creation
    Given I am on the "my_nice_sneakers" product page
    When I visit the "Completeness" column tab
    Given I am on the "my_nice_sneakers" product page
    When I visit the "Completeness" column tab
    Then I should see the text "No family defined. Please define a family to calculate the completeness of this product."
    And I change the family of the product to "Sneakers"
    And I should not see the text "No family defined. Please define a family to calculate the completeness of this product."

  @jira https://akeneo.atlassian.net/browse/PIM-6277
  Scenario: Display the channel code in the completeness panel
    Given I am on the "sneakers" product page
    And I switch the locale to "fr_FR"
    When I visit the "Completeness" column tab
    Then I should see the "tablet" completeness in position 1
    Then The label for the "tablet" channel should be "Tablette"
    When I am on the "tablet" channel page
    Then I fill in the following information:
      | French (France) |  |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I am on the "sneakers" product page
    When I visit the "Attributes" column tab
    And I switch the locale to "fr_FR"
    And I visit the "Completeness" column tab
    Then The label for the "tablet" channel should be "[tablet]"
