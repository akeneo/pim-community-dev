Feature: Onboarder Serenity - Authentication - validate a contributor password

  @onboarder-serenity-contributor-authentication-enabled
  Scenario: Update the contributor password
    Given a contributor account with email "test@test.test"
    When I update the contributor account with email "test@test.test" by updating the password to "P@$$w0rd"
    Then the contributor account with email "test@test.test" should have "P@$$w0rd" as password

  Scenario: Do not update the contributor password if it does not contain any digit character
    Given a contributor account with email "test@test.test"
    When I update the contributor account with email "test@test.test" by updating the password to "P@$$word"
    Then I should have the following errors while validating:
      | path  | message                                       |
      |       | The password should have at least one number. |

  Scenario: Do not update the contributor password if it does not contain an uppercase letter
    Given a contributor account with email "test@test.test"
    When I update the contributor account with email "test@test.test" by updating the password to "p@$$w0rd"
    Then I should have the following errors while validating:
      | path  | message                                               |
      |       | The password must have at least one uppercase letter. |

  Scenario: Do not update the contributor password if it does not contain a lowercase letter
    Given a contributor account with email "test@test.test"
    When I update the contributor account with email "test@test.test" by updating the password to "P@$$W0RD"
    Then I should have the following errors while validating:
      | path  | message                                               |
      |       | The password must have at least one lowercase letter. |

  Scenario: Do not update the contributor password if it does not have at least 8 characters
    Given a contributor account with email "test@test.test"
    When I update the contributor account with email "test@test.test" by updating the password to "P@5s"
    Then I should have the following errors while validating:
      | path  | message                                         |
      |       | The password should have 8 characters at least. |

  Scenario: Do not update the contributor password if it has more than 255 characters
    Given a contributor account with email "test@test.test"
    When I update the contributor account with email "test@test.test" by updating the password to "P@5s168468476541657465168461567864517485678945617894651248645123098465112489567436156786451748567894561789465124864512309846511248956743615678645174856789456178946512486451230984651124895674361567864517485678945617894651248645123098465112489567431248956743"
    Then I should have the following errors while validating:
      | path  | message                                        |
      |       | The password should not exceed 255 characters. |
