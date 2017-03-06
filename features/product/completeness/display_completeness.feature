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
      | sku      | family   | manufacturer | weather_conditions | color | name-en_US | name-fr_FR  | price          | rating | size | lace_color  |
      | sneakers | sneakers | Converse     | hot                | blue  | Sneakers   | Espadrilles | 69 EUR, 99 USD | 4      | 43   | laces_white |
      | sandals  | sandals  |              |                    | white |            | Sandales    |                |        |      |             |
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
    When I open the "Completeness" panel
    Then I should see the "en_US" completeness in position 1
    And The completeness "fr_FR" should be closed
    And The completeness "en_US" should be opened
    And I should see the completeness:
      | channel | locale | state   | missing_values         | ratio |
      | mobile  | en_US  | success |                        | 100%  |
      | tablet  | en_US  | warning | Side view              | 89%   |
      | mobile  | fr_FR  | success |                        | 100%  |
      | tablet  | fr_FR  | warning | Description, Side view | 78%   |
    When I am on the products page
    Then I am on the "sandals" product page
    And the Name field should be highlighted
    And the Description field should be highlighted
    And the Manufacturer field should not be highlighted
    And the SKU field should not be highlighted
    And the Product information group should be highlighted
    And the Marketing group should be highlighted
    And the Sizes group should be highlighted
    And the Colors group should not be highlighted
    And the Media group should be highlighted
    And I open the "Completeness" panel
    Then I should see the "en_US" completeness in position 1
    And The completeness "fr_FR" should be closed
    And The completeness "en_US" should be opened
    When I switch the locale to "fr_FR"
    Then I should see the "fr_FR" completeness in position 1
    And The completeness "en_US" should be closed
    And The completeness "fr_FR" should be opened
    And I should see the completeness:
      | channel | locale | state   | missing_values                                                | ratio |
      | mobile  | fr_FR  | warning | [price], [size]                                               | 60%   |
      | tablet  | fr_FR  | warning | [price], [rating], [side_view], [size]                        | 50%   |
      | mobile  | en_US  | warning | [name], [price], [size]                                       | 40%   |
      | tablet  | en_US  | warning | [name], [description], [price], [rating], [side_view], [size] | 25%   |

  @jira https://akeneo.atlassian.net/browse/PIM-5190
  Scenario: Successfully display the completeness of the products with medias after a save
    Given I am on the "sneakers" product page
    And I save the product
    When I open the "Completeness" panel
    Then I should see the "en_US" completeness in position 1
    And The completeness "fr_FR" should be closed
    And The completeness "en_US" should be opened
    And I should see the completeness:
      | channel | locale | state   | missing_values         | ratio |
      | mobile  | en_US  | success |                        | 100%  |
      | tablet  | en_US  | warning | Side view              | 89%   |
      | mobile  | fr_FR  | success |                        | 100%  |
      | tablet  | fr_FR  | warning | Description, Side view | 78%   |
    When I am on the "sandals" product page
    And I save the product
    And I open the "Completeness" panel
    Then I should see the "en_US" completeness in position 1
    And The completeness "fr_FR" should be closed
    And The completeness "en_US" should be opened
    And I should see the completeness:
      | channel | locale | state   | missing_values                                    | ratio |
      | mobile  | en_US  | warning | Name, Price, Size                                 | 40%   |
      | tablet  | en_US  | warning | Name, Description, Price, Rating, Side view, Size | 25%   |
      | mobile  | fr_FR  | warning | Price, Size                                       | 60%   |
      | tablet  | fr_FR  | warning | Price, Rating, Side view, Size                    | 50%   |

  Scenario: Successfully display the completeness of the products in the grid
    Given I am on the products page
    And I switch the locale to "en_US"
    And I filter by "scope" with operator "equals" and value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 40%   |
    And I filter by "scope" with operator "equals" and value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 89%   |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 25%   |
    And I switch the locale to "fr_FR"
    And I filter by "scope" with operator "equals" and value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 60%   |
    And I filter by "scope" with operator "equals" and value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 78%   |
    Then the row "sandals" should contain:
     | column   | value |
     | complete | 50%   |

  Scenario: Successfully display the completeness of the product in the grid after product save (PIM-2916)
    Given I am on the "sneakers" product page
    And I visit the "Attributes" tab
    And I visit the "Media" group
    And I attach file "SNKRS-1C-s.png" to "Side view"
    And I save the product
    And I am on the products page
    And I switch the locale to "en_US"
    When I filter by "scope" with operator "equals" and value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    When I filter by "scope" with operator "equals" and value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    And I switch the locale to "fr_FR"
    When I filter by "scope" with operator "equals" and value "Mobile"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 100%  |
    When I filter by "scope" with operator "equals" and value "Tablet"
    Then the row "sneakers" should contain:
     | column   | value |
     | complete | 89%   |

  Scenario: Don't display the completeness if the family is not defined
    Given I am on the "sneakers" product page
    When I open the "Completeness" panel
    Then I change the family of the product to ""
    And I should see the text "No family defined. Please define a family to calculate the completeness of this product."
    Then I change the family of the product to "Sneakers"
    And I should not see the text "No family defined. Please define a family to calculate the completeness of this product."
    Then I change the family of the product to "Boots"
    And I should see the text "You just changed the family of the product. Please save it first to calculate the completeness for the new family."

  @jira https://akeneo.atlassian.net/browse/PIM-4489
  Scenario: Don't display the completeness if the family is not defined on product creation
    Given the following products:
      | sku              |
      | my_nice_sneakers |
    And I am on the "my_nice_sneakers" product page
    When I open the "Completeness" panel
    Then I should see the text "No family defined. Please define a family to calculate the completeness of this product."
    Then I change the family of the product to "Sneakers"
    Then I should see the text "You just changed the family of the product. Please save it first to calculate the completeness for the new family."
    Then I should not see "No family defined. Please define a family to calculate the completeness of this product."
    And I save the product

  Scenario: Quickly jump to a field from completeness panel
    Given I am on the "sneakers" product page
    When I open the "Completeness" panel
    And I click on the missing "side_view" value for "en_US" locale and "tablet" channel
    Then I should be on the "Media" attribute group
