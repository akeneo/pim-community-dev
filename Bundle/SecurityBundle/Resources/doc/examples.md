Examples
========

**John** and **Mary** was created and was assigned to  **Main business unit**.

**Mike** was created and was assigned to child **Child business unit**.

And **Robert** was created and was assigned to in **Second business unit**.

John was create **Account A**,  Mary was create **Account B**, Mike was create **Account C**, Robert was create **Account D**,.

![example structure][1]

User ownersip type
---------

 Ownreship type for accounts is **User**.

**John** has access:

 - only to Account A on User access level
 - to Account A and Account B on Business Unit access level
 - to Account A,  Account B and Account C and on Division access level
 - to all accounts on Organization and System access levels.

**Mary** has access:

 - only to Account B on User access level
 - to Account A and Account B on Business Unit access level
 - to Account A,  Account B and Account C and on Division access level
 - to all accounts on Organization and System access levels.

**Mike** has access:

 - only to Account C on User, Business unit and Division access levels
 - to all accounts on Organization and System access levels.

**Robert** has access:

 - only to Account D on User, Business unit and Division access levels
 - to all accounts on Organization and System access levels.

Business Unit ownersip type
---------

 Ownreship type for accounts is **Business Unit**.

**John** and **Mary** has access:

 - to Account A and Account B on Business Unit access level
 - to Account A,  Account B and Account C and on Division access level
 - to all accounts on Organization and System access levels.

**Mike** has access:

 - only to Account C on Business Unit and Division access levels
 - to all accounts on Organization and System access levels.

**Robert** has access:

 - only to Account D on Business Unit and Division access levels
 - to all accounts on Organization and System access levels.

We can't set User access level because owrership type is Business Unit.

  [1]: img/example.png