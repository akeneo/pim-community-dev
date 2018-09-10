@javascript
Feature: Define the attribute requirement
  In order to ensure product completeness when exporting them
  As an administrator
  I need to be able to define which attributes are required or not for a given channel

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the "Boots" family page

  @critical
  Scenario: Successfully display the attribute requirements
    Given I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    And attribute "lace_color" should not be required in channels mobile and tablet
    And attribute "side_view" should be required in channel tablet
    And attribute "side_view" should not be required in channel mobile

  @critical
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

    @TODO move to enrichment
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
    And I wait for the "compute_completeness_of_products_family" job to finish
    When I am on the "BIGBOOTS" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | missing_values | ratio |
      | mobile  | en_US  | success | 0              | 100%  |
      | mobile  | fr_FR  | warning | 1              | 80%   |
      | tablet  | en_US  | warning | 3              | 62%   |

  @jira https://akeneo.atlassian.net/browse/PIM-7312
  Scenario: Successfully add an attribute requirement for a newly created channel
    Given the following channel:
      | code      | label-en_US | currencies | locales | tree            |
      | ecommerce | Ecommerce   | EUR,USD    | en_US   | 2014_collection |
    When I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    But attribute "name" should not be required in channel ecommerce
    When I switch the attribute "name" requirement in channel "ecommerce"
    And I save the family
    Then I should not see the text "There are unsaved changes."
    And attribute "name" should be required in channel ecommerce

  @jira https://akeneo.atlassian.net/browse/PIM-7718
  Scenario: Successfully add an attribute requirement for a newly created channel
    Given the following channel:
      | code      | label-en_US | currencies | locales | tree            |
      | ecommerce | Ecommerce   | EUR,USD    | en_US   | 2014_collection |
    When I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    But attribute "name" should not be required in channel ecommerce
    When I switch the attribute "name" requirement in channel "ecommerce"
    And I save the family
    Then I should not see the text "There are unsaved changes."
    And attribute "name" should be required in channel ecommerce
