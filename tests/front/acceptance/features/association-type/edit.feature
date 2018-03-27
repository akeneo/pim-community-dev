Feature: Displaying the association type edit form
    Scenario: Display the edit form
        Given the locales "en_US, fr_FR"
        And the edit form for association type "Cross sell" is displayed
        Then the title of the page should be "Cross sell"
        And the association type code should be "X_SELL"
