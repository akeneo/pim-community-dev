# Frontend documentation reference

- Project architecture
  - [Architecture overview & history](#architecture-overview-&-history)
  - [Legacy (Backbone, BEM, behat, …)](#legacy-stack)
  - [React](#react-stack)
  - [Akeneo Design System](#akeneo-design-system)
  - [Translations](#translations)
- Getting started
  - [Frontend developer toolkit](./)
  - Cookbooks
    - [How to create an independant frontend package](./package_setup.yml)
    - [Adding react inside backbone](./)
    - [Develop with react](./)
    - [Working on backbone directly](./)
    - [Migrating javascript backbone to typescript](./)
  - [FAQ for new contributors](#faq-for-new-contributors)
- Annex
  - [Yarn workspaces](./yarn_workspaces.md)

# Architecture overview & history

Akeneo PIM started with the framework [Symfony](https://symfony.com/) v2.1 and OroPlatform, using twig templates and RequireJS to load javascript modules.

Those javascript modules use Backbone to render views and the `form_extensions.yml` configurations to allow contributors to compose and extend the UI.

The Block Element Modifier (BEM) methodology was introduced, with the goal of rewriting and organize properly all the CSS stylesheets going forward.

Typescript was introduced for new development to beneficy from the static typing and avoid a whole class of bugs and javascript is rarely used directly anymore. The team started to convert javascript module to typescript when necessary.

The team introduced React to develop new features or easily embed components inside existing backbone pages.
The goal with React was to get closer to the mainstream stacks and improve the developer experience as well as the onboarding of new teammates. Also, with a SaaS first approache, the ability to extends directly the PIM UI is gone.

The new Design System created by our UX team started to be implemented in React and allow to re-use standard components everywhere. The BEM was deprecated as the new Design System become the source of truth for the UI style guide.

# Legacy stack

## Backbone

`requirejs.yml` files define javascript or typescript modules, they need to be in the `Resources/config/` folder of the Symfony bundle.

In the article available
[medium.com/akeneo-labs/akeneo-pim-frontend-guide](https://medium.com/akeneo-labs/akeneo-pim-frontend-guide-part-1-bd398b6483a2)

Schema of the relations between requirejs, form-extensions, data-drop-zone, etc...

## BEM

Explanation of the BEM methodology & conventions (point to an external resource).

[https://docs.akeneo.com/4.0/design_pim/styleguide/index.html#Overview](https://docs.akeneo.com/4.0/design_pim/styleguide/index.html#Overview)

How to update the BEM, what can I do / not do.

The BEM is not the source of truth, the DSM is!

## Legacy Tests

Quick explanation about _Behat legacy_ used to test the frontend with e2e and the fact that it's deprecated.

# React stack

List the external libs that can be used, but they are not mandatories (not every form need `react-hook-form`)

External libs:

- `react-hook-form` (not `formik`)
  [https://react-hook-form.com/](https://react-hook-form.com/)
- `styled-component`
  [https://styled-components.com/](https://styled-components.com/)
  Explain that it replace the BEM or local css for the React stack.
  Quick example on how to extends existing component (custom or from the DSM) to add styling.
- use es6 `fetch` to call the internal api
  [https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)
- `redux` , `redux-thunk` (EE)
  [https://redux.js.org/](https://redux.js.org/)
- `react-router`
  [https://reactrouter.com/](https://reactrouter.com/)
- `victory`
  [https://formidable.com/open-source/victory/](https://formidable.com/open-source/victory/)
- `jquery`
  Try to avoid jQuery.

Internal libs:

- **shared** (common services: router, etc...)
  [https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared](https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/shared)
  README.md need to be added.
- **legacy bridge**
  [https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge](https://github.com/akeneo/pim-community-dev/tree/master/src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge)
  README.md need to be added.
- **DSM**
  See the DSM section.

## React Tests

We need documentation and good example of some tests.

- jest
- jest-fetch-mock
- enzyme
- react-test-renderer
- cucumber (deprecated)

# Akeneo Design System

Quick explanation of the DSM goal and link to the doc:

Point to resource on notion (workflow, reference, etc...)

[https://github.com/akeneo/pim-community-dev/tree/master/akeneo-design-system](https://github.com/akeneo/pim-community-dev/tree/master/akeneo-design-system)

# Translations

Translation files are part of the `Resources/translations/` folder of each Symfony bundle, the only file that need to be updated is `jsmessages.en_US.yml` (translation are handled by crowndin)

To see the updated the translation keys you need to rebuild a part of the frontend:

```bash
rm -r var/cache
make assets
make javascript-dev
```

# FAQ for new contributors

## How to get help?

### Slack channel

You can join the **#pim-frontend** channel on slack to ask questions related to the PIM. But it's also a good place to share ideas and improvements with your teammates.

### Frontend guild

There is a guild that regroup teammates from every squads to discuss ongoing initiatives or suggest ideas for the evolution of the PIM frontend stack.

Meetings are scheduled every two weeks and you can ask **@samir.boulil** or your engineering manager to add you to the guild events.

Previous meetings notes are available to everyone [here](https://www.notion.so/akeneo/b9f094e2f6ce442389426a0b70cee812).
