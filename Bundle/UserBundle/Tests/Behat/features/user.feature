# features/user.feature
Feature: User
  In order to create users
  As a OroCRM Admin user
  I need to be able to open Create User dialog and create new user
Scenario: Create new user
  Given Login as an existing "admin" user and "admin" password
  And I open "User Create" dialog
  When I fill in user form:
        | FIELD      | VALUE    |
        | username   | userName |
        | enabled    | true     |
        | password   | 123123q  |
        | first name | First Name |
        | last name  | Last Name  |
        | email      | email@test.com |
        | roles      | User           |
  And I press "Save And Close"
  Then I should see "User successfully saved"
