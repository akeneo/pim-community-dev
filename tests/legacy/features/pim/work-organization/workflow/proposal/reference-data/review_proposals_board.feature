@javascript
Feature: Review proposals on board
  In order to accept product changes
  As an administrator
  I need to be able to review product draft for approval

  Background:
    Given a "clothing" catalog configuration
    And the following "sleeve_fabric" attribute reference data: PVC, Nylon, Neoprene, Lace
    And the following "lace_color" attribute reference data: Red, Green, Black, White

  Scenario: Successfully review a proposal with a new simple select value
    Given the product:
      | family     | hoodies    |
      | categories | winter_top |
      | sku        | my-hoody   |
      | lace_color |            |
    And Mary proposed the following change to "my-hoody":
      | tab   | field      | value |
      | Other | Lace color | Black |
    And I am logged in as "Julia"
    When I am on the proposals page
    Then I should see the following proposals:
      | product  | author | attribute  | original | new   |
      | my-hoody | Mary   | lace_color |          | Black |
