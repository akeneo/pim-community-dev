Feature:
  In order to distribute relevant products
  As Julia
  I want to manage the product selection using an UI

  @browser
  Scenario: visiting the frontpage
    When I visit duckduckgo.com
    Then I should see a search bar
