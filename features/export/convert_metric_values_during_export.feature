Feature: Convert metric values during export
  In order to homogeneize exported metric values
  As Julia
  I need to be able to define in which unit to convert metric values during export

  Scenario: Succesfully display metric conversion configuration for a channel
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the "tablet" channel page
    And I should see "Conversion Options" fields:
      | Area        |
      | Binary      |
      | Frequency   |
      | Length      |
      | Power       |
      | Speed       |
      | Temperature |
      | Volume      |
      | Weight      |
