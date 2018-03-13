Feature: Displaying the association type edit form
    Scenario: Display the edit form
        Given the locales "en_US, fr_FR"
        And the edit form for association type "X_SELL" is displayed
        Then the title of the page should be "Cross1 sell"
        And the association type code should be "X_SELL"
