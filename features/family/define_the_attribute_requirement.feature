@javascript
Feature: Define the attribute requirement
  In order to ensure product completeness when exporting them
  As an administrator
  I need to be able to define which attributes are required or not for a given channel

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the "Boots" family page

  Scenario: Successfully display the attribute requirements
    Given I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    And attribute "lace_color" should not be required in channels mobile and tablet
    And attribute "side_view" should be required in channel tablet
    And attribute "side_view" should not be required in channel mobile

  Scenario: Successfully make an attribute required for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "rating" requirement in channel "mobile"
    And I save the family
    And I should see the flash message "Family successfully updated"
    And I should not see the text "There are unsaved changes."
    And I visit the "Attributes" tab
    Then attribute "rating" should be required in channels mobile and tablet

  Scenario: Successfully make an attribute optional for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "description" requirement in channel "tablet"
    And I save the family
    And I should see the flash message "Family successfully updated"
    And I should not see the text "There are unsaved changes."
    And I visit the "Attributes" tab
    Then attribute "description" should not be required in channels mobile and tablet

  Scenario: Ensure attribute requirement removal
    Given the following product:
      | sku      | family | name-en_US | price          | size | color |
      | BIGBOOTS | Boots  | Big boots  | 20 EUR, 20 USD | 35   | Black |
    And I launched the completeness calculator
    When I am on the "BIGBOOTS" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | mobile  | en_US  | success | 0              | 100%  |
      | mobile  | fr_FR  | warning | 1              | 80%   |
      | tablet  | en_US  | warning | 4              | 55%   |
    And I am on the "Boots" family page
    And I visit the "Attributes" tab
    And I switch the attribute "rating" requirement in channel "mobile"
    And I save the family
    And I should see the flash message "Family successfully updated"
    And I should not see the text "There are unsaved changes."
    When I remove the "rating" attribute
    And I save the family
    And I should not see the text "There are unsaved changes."
    Then I should not see the "rating" attribute
    When I launched the completeness calculator
    When I am on the "BIGBOOTS" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | mobile  | en_US  | success | 0              | 100%  |
      | mobile  | fr_FR  | warning | 1              | 80%   |
      | tablet  | en_US  | warning | 3              | 62%   |
