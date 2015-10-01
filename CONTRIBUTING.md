Contributor
-----------

Thank you to read and sign the following contributor agreement http://www.akeneo.com/contributor-license-agreement/

Every PR should start with:

```
The description of what the PR does

| Q                 | A
| ----------------- | ---
| Added Specs       |
| Added Behats      |
| Travis CI is ok   |
| Changelog updated |
```

We're trying hard to ease the external contributions, don't hesitate to open an issue to start a discussion, a core developer will help you.

Core Developer
--------------

Every PR should start with:

For a Story,

```
| Q                 | A
| ----------------- | ---
| Specs             |
| Behats            |
| Blue CI           |
| Changelog updated |
| Review and 2 GTM  |
| Micro Demo (PO)   |
| Migration script  |
| Tech Doc          |
```

For a Bug Fix,

```
| Q                 | A
| ----------------- | ---
| Specs             |
| Behats            |
| Blue CI           |
| Changelog updated |
| Review and 2 GTM  |
```

Extra info
----------

Specs means phpspec have been written, every classes are specced except controllers, form types, commands, doctrine entity (POPO), symfony semantic config.

Behats means behat scenario have been written, for nominal and limit cases, internal api can be also tested through behat via commands (like query or updater).

Blue CI means,
* Github status is ok on the PR (travisci runs static + unit tests)
* Jenkins behat builds are blue (CE & EE, Orm & MongoDB)

Changelog updated means the bug fix line has been added (in case of bug) via an explicit sentence, all the BC break (with the last minor version) have been listed.

Review and 2 GTM means the technical review has been done, comments have been fixed and two teamates at least have given a Good To Merge.

Micro Demo (PO) means the micro demo has been done to the Product Owner and the story has been validated.

Migration script means we changed the data model and we provides migration script allowing to migrate data from previous minor version to the coming one.

Tech Doc means cookbook and reference doc has been written.
