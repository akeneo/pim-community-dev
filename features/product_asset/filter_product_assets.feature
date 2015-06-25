@javascript
Feature: Filter product assets
  In order to easily manage product assets
  As a product manager
  I need to be able to filter product assets by several columns

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the assets page

  Scenario: Successfully sort product assets
    And I should be able to use the following filters:
      | filter        | value                             | result                                          |
      | Code          | contains ac9                      | AC9887, AC9856, AC9969                          |
      | Code          | is equal to ac1147                | AC1147                                          |
      | Description   | contains back                     | Back of super hero t-shirt                      |
      | Description   | does not contain t-shirt          | AC8600, AC9887, AC9856, AC1147, AC6667, AC9969  |
      | End of use    | between 2006-01-01 and 2008-01-01 | AC9969                                          |
      | End of use    | more than 2020-01-01              | AC9887, AC9856                                  |
      | End of use    | less than 2030-01-01              | AC9887, AC9969                                  |
      | Tags          | in list women                     | AC2230                                          |
      | Tags          | in list lacework,men              | AC9856, AC6667                                  |
      | Tags          | is empty                          | AC6656                                          |
