Feature: Define the attribute requirement
  In order to ensure product completness when exporting them
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

  @javascript
  Scenario: Successfully make an attribute required for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "Rating" requirement in channel "Mobile"
    And I save the family
    And I visit the "Attributes" tab
    Then attribute "rating" should be required in channels mobile and tablet

  @javascript
  Scenario: Successfully make an attribute optional for a channel
    Given I visit the "Attributes" tab
    And I switch the attribute "Description" requirement in channel "Tablet"
    And I save the family
    And I visit the "Attributes" tab
    Then attribute "description" should not be required in channels mobile and tablet

  @javascript
  Scenario: Ensure attribute requirement removal
    Given the following product:
      | sku      | family | name-en_US | price          | size | color |
      | BIGBOOTS | Boots  | Big boots  | 20 EUR, 20 USD | 35   | Black |
    And I launched the completeness calculator
    When I am on the "BIGBOOTS" product page
    And I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | success | Complete         | 100%  |
      | tablet  | English (United States) | warning | 4 missing values | 56%   |
    And I am on the "Boots" family page
    And I visit the "Attributes" tab
    And I switch the attribute "Rating" requirement in channel "Mobile"
    And I save the family
    When I remove the "Rating" attribute
    Then I should not see the "Rating" attribute
    When I launched the completeness calculator
    When I am on the "BIGBOOTS" product page
    And I visit the "Completeness" tab
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale                  | state   | message          | ratio |
      | mobile  | English (United States) | success | Complete         | 100%  |
      | tablet  | English (United States) | warning | 3 missing values | 63%   |
