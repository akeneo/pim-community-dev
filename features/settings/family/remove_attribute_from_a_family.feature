@javascript
Feature: Remove attribute from a family
  In order to correct myself when I have wrongly added an attribute into a family
  As an administrator
  I need to be able to remove an attribute from a family

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code |
      | Bags |
    And the following attributes:
      | label            | families |
      | Long Description | Bags     |
      | Manufacturer     | Bags     |
    And the following product:
      | sku            | family | longDescription | manufacturer |
      | bag-dolce-vita | Bags   | my description  | dolce        |
      | bag-noname     | Bags   | some random bag |              |
    And I am logged in as "Peter"

  Scenario: Successfully remove an attribute from a family and display it as removable from product
    Given I am on the "Bags" family page
    And I visit the "Attributes" tab
    When I remove the "Manufacturer" attribute
    And I confirm the deletion
    Then I should see flash message "Attribute successfully removed from the family"
    And I should see attribute "Long Description" in group "Other"
    When I am on the "bag-dolce-vita" product page
    Then I should see a remove link next to the "Manufacturer" field

  @skip
  Scenario: Successfully update product completeness when removing a required attribute from a family
    Given I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I switch the attribute "Manufacturer" requirement in channel "E-Commerce"
    And I switch the attribute "Manufacturer" requirement in channel "Mobile"
    And I save the family
    When I launched the completeness calculator
    And I am on the "bag-noname" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel    | locale                  | state    | message         | ratio |
      | e-commerce | English (United States) | warning  | 1 missing value | 50%   |
      | e-commerce | French (France)         | warning  | 1 missing value | 50%   |
      | mobile     | English (United States) | disabled | none            | none  |
      | mobile     | French (France)         | warning  | 1 missing value | 50%   |
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I remove the "Manufacturer" attribute
    Then I should see flash message "Attribute successfully removed from the family"
    When I am on the "bag-noname" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel    | locale                  | state    | message            | ratio |
      | e-commerce | English (United States) |          | Not yet calculated |       |
      | e-commerce | French (France)         |          | Not yet calculated |       |
      | mobile     | English (United States) | disabled | none               | none  |
      | mobile     | French (France)         |          | Not yet calculated |       |
    When I launched the completeness calculator
    And I am on the "bag-noname" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel    | locale                  | state    | message  | ratio |
      | e-commerce | English (United States) | success  | Complete | 100%  |
      | e-commerce | French (France)         | success  | Complete | 100%  |
      | mobile     | English (United States) | disabled | none     | none  |
      | mobile     | French (France)         | success  | Complete | 100%  |
