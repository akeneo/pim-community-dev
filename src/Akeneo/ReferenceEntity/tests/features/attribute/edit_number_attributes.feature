Feature: Edit a number attribute of a reference entity
  In order to edit the properties of a number attribute
  As a user
  I want to be able to edit a number attribute

  @acceptance-back
  Scenario: Updating the label
    Given a reference entity with a number attribute 'area' and the label 'en_US' equal to 'Area'
    When the user updates the 'area' attribute label with '"Superficie"' on the locale '"en_US"'
    Then the label 'en_US' of the 'area' attribute should be 'Superficie'

  @acceptance-back
  Scenario: Updating is decimal property
    Given a reference entity with a number attribute 'area' non decimal
    When the user sets the 'area' attribute to have decimal values
    Then 'area' could have decimal values

  @acceptance-back
  Scenario Outline: Invalid is decimal edit
    Given a reference entity with a number attribute 'area' non decimal
    When the user sets the is decimal property of the 'area' attribute to '<invalid_decimals_allowed>'
    Then there should be a validation error on the property 'decimalsAllowed' with message '<message>'

    Examples:
      | invalid_decimals_allowed | message                        |
      | null                   | This value should not be null. |

  @acceptance-back
  Scenario: Updating the min value property
    Given a reference entity with a number attribute 'area' no min value
    When the user sets the min value of 'area' to 10
    Then 'area' min value should be 10

  @acceptance-back
  Scenario: Unsetting the min value
    Given a reference entity with a number attribute 'area' with a min value
    When the user unsets the min value of 'area'
    Then 'area' should not have a min value

  @acceptance-back
  Scenario Outline: Invalid min value edit
    Given a reference entity with a number attribute 'area' no min value
    When the user sets the min value of the 'area' attribute to '<invalid_min_value>'
    Then there should be a validation error with message '<message>'

    Examples:
      | invalid_min_value | message                        |
      | "not an integer"  | This value should be a number. |

  @acceptance-back
  Scenario: Updating the max value property
    Given a reference entity with a number attribute 'area' no max value
    When the user sets the max value of 'area' to 10
    Then 'area' max value should be 10

  @acceptance-back
  Scenario: Unsetting the max value
    Given a reference entity with a number attribute 'area' with a max value
    When the user unsets the max value of 'area'
    Then 'area' should not have a max value

  @acceptance-back
  Scenario Outline: Invalid max value edit
    Given a reference entity with a number attribute 'area' no min value
    When the user sets the max value of the 'area' attribute to '<invalid_max_value>'
    Then there should be a validation error with message '<message>'

    Examples:
      | invalid_max_value | message                        |
      | "not an integer"  | This value should be a number. |

  @acceptance-back
  Scenario: Min value should not be greater than the max value
    Given a reference entity with a number attribute 'area'
    When the user sets the min value of 'area' to 201 and the max value to 0
    Then there should be a validation error with message 'The min cannot be greater than the max'
