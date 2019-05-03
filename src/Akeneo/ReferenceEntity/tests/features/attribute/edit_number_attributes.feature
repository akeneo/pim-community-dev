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
