Feature: Create a new user
  In order to manage application users
  As an admin
  I need to be able to create a new user

  @acceptance-back
  Scenario: Validate the username upon user creation
    When a user is created with username "foo bar"
    Then the error "The username should not contain space character." is raised
