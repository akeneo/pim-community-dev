# Decisions

_This document keeps track of all the discussions and decisions we took on the subject and should be updated each time we discuss about a subject worth remembering._

## 10/08/2018

### Validation of commands

Right now we are validating the command given by the action to give feedback to the user. In the case of the creation of an attribute, the user cannot edit all properties. The backend define default values for those properties during the creation of the command and then we validate it.

**This situation can lead to some problems**: if the backend is not well coded, we can be in a situation when the user get messages about fields that he cannot edit.

**Solution**: we think that maybe we should have different validation groups (one for the creation and one for the edition) and define the default values deeper in the stack (the command handler maybe). That way, there will be no chance that the user get errors on fields that he cannot manage.

### Strategy of tests for validation

The validation is an important subject for the testing strategy: it's involving both the backend and the frontend and the contract between them is key. Right now, we are testing the validation with integration tests with in memory repositories for the backend and acceptance tests in the frontend.

**Problem with this solution**: this is really verbose and we are testing all validation cases using http requests. Also the responses are not shared between the frontend and the backend which can lead to mis-alignment.

**Solution**: We think that we should use JSON files to share the responses expected by the backend and the frontend. To do so, we will have one file per response with to mandatory keys: code (string) and body (JSON). This work is potentially huge so we can say that for the next cards we will implement this technique and see how it goes.
