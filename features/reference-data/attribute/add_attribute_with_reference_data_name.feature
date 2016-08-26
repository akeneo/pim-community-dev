@javascript
Feature: Perform form validation when creating a reference data attribute
  In order to check the "reference data name" field is filled
  As a product manager
  I need to be sure I have selected the "reference data name" for the attribute I'm creating

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  @jira https://akeneo.atlassian.net/browse/PIM-5896
  Scenario: Avoid creation of a simple reference data when field reference data name is not filled
    Given I create a "Reference data simple select" attribute
    And I fill in the following information:
      | Code            | mycolor |
      | Attribute group | Other   |
    When I save the attribute
    Then I should see validation error "This value should not be blank."

  @jira https://akeneo.atlassian.net/browse/PIM-5896
  Scenario: Avoid creation of a multiple reference data when field reference data name is not filled
    Given I create a "Reference data multi select" attribute
    And I fill in the following information:
      | Code            | mycolor |
      | Attribute group | Other   |
    When I save the attribute
    Then I should see validation error "This value should not be blank."
