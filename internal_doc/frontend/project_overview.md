# Project overview

- [Frontend architecture history](#frontend-architecture-history)
- [Legacy stack](#legacy-stack)
  - [BEM](#bem-deprecated)
- [React stack](#react-stack)
  - [External packages](#external-packages)
  - [Internal packages](#internal-packages)
  - [React tests](#react-tests)
- [Akeneo Design System](#akeneo-design-system)
- [Translations](#translations)

## Frontend architecture history

1. Akeneo PIM was created with the framework [Symfony](https://symfony.com/) (2.1) and OroPlatform, using twig templates and RequireJS to load javascript modules.

   Those javascript modules used Backbone to render views and "form extensions" to compose and extend the UI.

1. The Block Element Modifier (BEM) methodology was introduced, with the goal of rewriting and organize properly all the CSS stylesheets going forward.

1. Typescript was introduced for new development to beneficy from the static typing and avoid a whole class of bugs, as a result javascript is rarely used directly anymore.

   The team started to convert javascript module to typescript when necessary.

1. The team introduced React to develop new features or easily embed components inside existing backbone pages.

   The goal with React was to get closer to the mainstream stacks and improve the developer experience as well as the onboarding of new teammates. Also, with a SaaS first approache, the ability to extends directly the PIM UI is gone.

1. The new [Design System](https://en.wikipedia.org/wiki/Design_system) created by our UX team started to be implemented in React and allow to re-use standard components everywhere.

   The BEM was deprecated as the new Design System become the source of truth for the UI style guide.

## Legacy stack

<!-- ## Backbone

`requirejs.yml` files define javascript or typescript modules, they need to be in the `Resources/config/` folder of the Symfony bundle.

In the article available
[medium.com/akeneo-labs/akeneo-pim-frontend-guide](https://medium.com/akeneo-labs/akeneo-pim-frontend-guide-part-1-bd398b6483a2)

Schema of the relations between requirejs, form-extensions, data-drop-zone, etc... -->

### BEM

The "BEM" refer to the UI styles reimplemented in [less](http://lesscss.org/) and following the [Block Element Modifier methodology](http://getbem.com/).

The documentation is available on [docs.akeneo.com](https://docs.akeneo.com/4.0/design_pim/styleguide/index.html#Overview) (outdated).

As of now, the BEM is deprecated and **not** the source of truth for the UI styles, the DSM should be used instead whenever possible.

It is still allowed to use the BEM in Backbone views, and update it if necessary.

<!-- ## Legacy Tests

Quick explanation about _Behat legacy_ used to test the frontend with e2e and the fact that it's deprecated. -->

## React stack

### External packages

 :package: This is a list of some of the external libraries that exists inside the PIM.

- `react-hook-form`

  Documentation on [react-hook-form.com](https://react-hook-form.com/)

  Do not use `formik` (deprecated).

- `styled-component`

  Documentation on [styled-components.com](https://styled-components.com/)

- `redux` , `redux-thunk` (EE)

  Documentation on [redux.js.org](https://redux.js.org/)

- `react-router`

  Documentation on [reactrouter.com](https://reactrouter.com/)

- `victory`

  Victory to make charts. Documentation on [formidable.com](https://formidable.com/open-source/victory/)

- `jquery`

  Try to avoid jQuery. As of today, most of what jQuery offer can be done easily with the standard library.

- Use es6 `fetch` to make HTTP calls

  Documentation of the [Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)

These libraries are not mandatory to use (not every form require `react-hook-form` nor `redux`).

### Internal packages

- `@akeneo-pim-community/shared`

  [Sources](https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared)


- `@akeneo-pim-community/legacy-bridge`

  [Sources](https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge)


- `akeneo-design-system`

  Check the [Akeneo Design System](#akeneo-design-system) section.

### React Tests

TODO

## Akeneo Design System

Checkout the [Akeneo Design System Repository](https://github.com/akeneo/pim-community-dev/tree/master/akeneo-design-system#akeneo-design-system-repository) documentation.

## Translations

TODO
