# How to connect your micro frontend

Great, you followed the cookbook "[how to create a micro-frontend](./how_to_create_a_micro_front_end.md)" and now you want to integrate it in the PIM.

## Define what you want to expose to the PIM

One of the goal of micro frontend architecture is to reduce coupling between features. So it's important to choose wisely what you want to expose to the PIM. If you [use the Create React App template](./how_to_create_a_micro_front_end.md), your folder structure should look something like this:

```
package.json
lib/            <- The compiled code of the micro frontend
src/            <- The source code of the micro frontend
  feature/      <- The source code of the feature you want to expose to the PIM
    index.ts    <- You need to define here what you want to expose to the PIM
  index.tsx     <- The entry point of the micro frontend when run in isolation
```

You need to define in the `src/feature/index.ts` what you want to expose to the PIM. Here is an example of what it could look like:

```typescript
export * from './components/MeasurementApp';
```

In this case, your micro frontend will only expose the `MeasurementApp` component to the outside (the PIM).

## Use your micro frontend in the PIM

First you need to make sure that your package is declared as a workspace in the PIM `package.json` (as seen in [the previous cookbook](how_to_create_a_micro_front_end.md)).

```json
    # package.json of the PIM
    ...
    "workspaces": [
        "vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Measurement/front"
    ]
```

You now need to get the name of your package

```json
    # package.json of your micro-frontend
    "name": "@akeneo-pim-enterprise/measurement"
```

You can now import it in the PIM:

```typescript

import {MeasurementApp} from '@akeneo-pim-enterprise/measurement';

ReactDom.render(<MeasurementApp />, document.getElementById('root'));
```

## Usefull tools

### Dependency provider

To ease the integration of micro frontend with the PIM, you can use the `<DependencyProvider>`. It's a React component using the Context API to inject PIM shared dependencies (router, translator, security context, etc).
Here is how to use it:

```typescript
import {MeasurementApp} from '@akeneo-pim-enterprise/measurement';
import {DependencyProvider} from '@akeneo-pim-community/legacy-bridge';

ReactDom.render(
    <DependencyProvider>
      <MeasurementApp />
    </DependencyProvider>,
  document.getElementById('root')
);
```

### React controller

If your micro frontend represents a full page or a full feature of the PIM, it's most likely that you need to use the React controller. It's a Backbone controller intended to boot a single React component into the PIM.
You can take a look at this [live example in the codebase](https://github.com/akeneo/pim-community-dev/blob/b29ccfc0015464884fac02ccc997f7c333e4b96b/src/Akeneo/Tool/Bundle/MeasureBundle/Resources/public/bridge/controller/settings.tsx).
