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
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | success | Complete         | 100%  |
      | mobile  | French (France)         | success | Complete         | 100%  |
      | tablet  | English (United States) | warning | 1 missing value  | 89%   |
      | tablet  | French (France)         | warning | 2 missing values | 78%   |
    When I am on the "sandals" product page
    And I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | warning | 3 missing values | 40%   |
      | mobile  | French (France)         | warning | 2 missing values | 60%   |
      | tablet  | English (United States) | warning | 6 missing values | 25%   |
      | tablet  | French (France)         | warning | 4 missing values | 50%   |

  Scenario: Successfully display the completeness of the products in the grid
    Given I am on the products page
    And I switch the locale to "English (United States)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 100%  |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 40%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 89%  |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 25%  |
    And I switch the locale to "French (France)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 100%  |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 60%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 78%  |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 50%  |

  Scenario: Successfully update the completeness at product save
    Given I am on the "sneakers" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | success | Complete         | 100%  |
      | mobile  | French (France)         | success | Complete         | 100%  |
      | tablet  | English (United States) | warning | 1 missing value  | 89%   |
      | tablet  | French (France)         | warning | 2 missing values | 78%   |
    When I visit the "Attributes" tab
    And I visit the "Media" group
    And I attach file "SNKRS-1C-s.png" to "Side view"
    And I save the product
    Then I should be on the product "sneakers" edit page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | success | Complete         | 100%  |
      | mobile  | French (France)         | success | Complete         | 100%  |
      | tablet  | English (United States) | success | Complete         | 100%  |
      | tablet  | French (France)         | warning | 1 missing value  | 89%   |

  Scenario: Successfully display the completeness of the product in the grid after product save (PIM-2916)
    Given I am on the "sneakers" product page
    And I visit the "Attributes" tab
    And I visit the "Media" group
    And I attach file "SNKRS-1C-s.png" to "Side view"
    And I save the product
    And I am on the products page
    And I switch the locale to "English (United States)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 100%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 100%  |
    And I switch the locale to "French (France)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 100%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 89%   |

  Scenario: Remove completeness when family requirements changed
    Given I am on the "sneakers" family page
    And I visit the "Attributes" tab
    And I switch the attribute "Rating" requirement in channel "Mobile"
    And I save the family
    And I am on the "sneakers" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state | message            | ratio |
      | mobile  | English (United States) |       | Not yet calculated |       |
      | mobile  | French (France)         |       | Not yet calculated |       |
      | tablet  | English (United States) |       | Not yet calculated |       |
      | tablet  | French (France)         |       | Not yet calculated |       |
    When I am on the "sandals" product page
    And I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | warning | 3 missing values | 40%   |
      | mobile  | French (France)         | warning | 2 missing values | 60%   |
      | tablet  | English (United States) | warning | 6 missing values | 25%   |
      | tablet  | French (France)         | warning | 4 missing values | 50%   |

  Scenario: Remove completeness from grid when family requirements changed
    Given I am on the "sneakers" family page
    And I visit the "Attributes" tab
    And I switch the attribute "Rating" requirement in channel "Mobile"
    And I save the family
    And I am on the products page
    And I switch the locale to "English (United States)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    |   -   |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 40%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    |   -   |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    |  25%  |
    And I switch the locale to "French (France)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    |   -   |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 60%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    |   -   |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    |  50%  |

  Scenario: Remove completeness when locales of a channel are deleted
    Given I am on the "tablet" channel page
    And I change the "Locales" to "French (France)"
    And I press the "Save" button
    And I am on the "sneakers" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state    | message          | ratio |
      | mobile  | English (United States) | success  | Complete         | 100%  |
      | mobile  | French (France)         | success  | Complete         | 100%  |
      | tablet  | English (United States) | disabled | none             | none  |
      | tablet  | French (France)         | warning  | 2 missing values | 78%   |
    When I am on the "sandals" product page
    And I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state    | message          | ratio |
      | mobile  | English (United States) | warning  | 3 missing values | 40%   |
      | mobile  | French (France)         | warning  | 2 missing values | 60%   |
      | tablet  | English (United States) | disabled | none             | none  |
      | tablet  | French (France)         | warning  | 4 missing values | 50%   |

  Scenario: Remove completeness from grid when locales of a channel are deleted
    Given I am on the "tablet" channel page
    And I change the "Locales" to "French (France)"
    And I press the "Save" button
    And I am on the products page
    And I switch the locale to "English (United States)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 100%  |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 40%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    |   -   |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    |   -   |
    And I switch the locale to "French (France)"
    And I filter by "Channel" with value "Mobile"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    | 100%  |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    | 60%  |
    And I filter by "Channel" with value "Tablet"
    Then the row "sneakers" should contain:
     | column      | value |
     | complete    |  78%  |
    Then the row "sandals" should contain:
     | column      | value |
     | complete    |  50%  |

  @jira  https://akeneo.atlassian.net/browse/PIM-3386
  Scenario: Successfully update the completeness of a product that contains attribute locale specific attributes (PIM-3386)
    Given the following attributes:
      | code      | families | locales      | group |
      | text_fr   | sneakers | fr_FR        | info  |
      | text_en   | sneakers | en_US        | info  |
    And I am on the "sneakers" family page
    And I visit the "Attributes" tab
    And I switch the attribute "[text_fr]" requirement in channel "Mobile"
    And I switch the attribute "[text_en]" requirement in channel "Tablet"
    And I save the family
    And I am on the "sneakers" product page
    When I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | tablet  | English (United States) | warning | 2 missing values | 80%   |
      | tablet  | French (France)         | warning | 2 missing values | 80%   |
      | mobile  | English (United States) | success | Complete         | 100%  |
      | mobile  | French (France)         | warning | 1 missing values | 83%   |

