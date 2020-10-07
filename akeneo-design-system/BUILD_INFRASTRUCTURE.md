# Build infrastructure

## Introduction

When we started to work on the DSM we set a goal to ourselves to make it as easy as possible to contribute to it. We think that developer adoption will depend a lot on how easy contribution is made possible.

One of the main pain point when contributing is often not related to the code changes themselves but more about things around it:

- running tests
- review the changes
- build the project
- publish it

In this document we will exlain how we automated those tasks to empower anybody wishing to maintain and improve the system.

## On change request

When adding a new component or improving an existing one, we want to make sure that everything will work in production. To do so we have different gatekeepers:

- Unit tests launched by Jest
- Visual regression tests using Puppeteer and Jest
- Lint validation done by eslint and prettier

### Workflow

All those tests are launched by the github action located in the [.github/workflows/dsm-test.yml](https://github.com/akeneo/pim-community-dev/blob/master/.github/workflows/dsm-test.yml) file on the pim-community-dev repository. We will go over the important steps of this document but if you want to dive deeper, you can read it as it's self-documented.

This workflow launches on every pull requests targeting master with file modifications happening in the `akeneo-design-system` folder.

We start by preparing the build environments (checkout branch, node version), generate a dummy component to test it and then launch all test suites (lint, unit and visual regression tests). We then build the example integration project to check that the lib works well in our products in production.

### The visual regression problem

Visual regression tests are a complicated subject: checking that a component is rendering exactly the same as before on multiple OS is complicated. As every system renders fonts differently (even between version of the same system), it was impossible to run them locally.
To fix this problem we decided to only check them on the CI and ease the way to update them by automating the process:

- On pull request, if a [visual regression](https://jestjs.io/docs/en/snapshot-testing) test is failing
- We launch the tests again to update them
- Create a pull request with the updated snapshots targeting the current pull request
- The developer can then merge the proposed updates after UX designer validation
- Visual regression tests will now pass on the pull request

### Show your work

If everything went right, the Github action deploys the storybook to the Github page of the external repository in a subfolder (the pull request id is used as folder name). A comment with the link to the staging environment is then automatically added to the pull request 🧙🏼‍♂️.

## On pull request merge

After a pull request has been accepted and merged, we want to expose the new changes to external Akeneo products. To do so, we extract the `akeneo-design-system` subfolder from `pim-community-dev` to its dedicated [external repository](https://github.com/akeneo/akeneo-design-system).
This workflow is located in the [.github/workflows/dsm-extract.yml](https://github.com/akeneo/pim-community-dev/blob/master/.github/workflows/dsm-extract.yml) file. We will go over the important steps of this document but if you want to dive deeper, you can read it as it's self-documented.

### Workflow

The first two jobs run in parallel to build the lib and bump the lib version. Once it's done, we can extract it to the external repository.

### How do we bump the lib version

The important part here is to understand how we bump the version:

- We start by getting the current version number on the package.json from the external repository.
- Then we get all the commit messages on the merged pull request. We analyse their commit message to decide which version level we should bump (patch, minor, major).
- Once it's done, we can add the package.json before extracting the `akeneo-design-system` folder.

## After extracting

Once the akeneo design system has been extracted to the external repository we still need to tag it, release it to Github and publish it to NPM. We also need to update the Storybook to Github pages.

### Deploy storybook

The deploy workflow is located in the [akeneo-design-system/.github/workflows/deploy.yml](https://github.com/akeneo/pim-community-dev/blob/master/akeneo-design-system/.github/workflows/deploy.yml) file. It's a Github action building the lib and pushing it to the `gh-pages` branch.

### Tag and release

The tag and release workflow is located in the [akeneo-design-system/.github/workflows/tag-and-publish-to-npm.yml](https://github.com/akeneo/pim-community-dev/blob/master/akeneo-design-system/.github/workflows/tag-and-publish-to-npm.yml) file. It's a Github action commiting the tag and pushing it to `master` and then publishes it on the NPM registry.
