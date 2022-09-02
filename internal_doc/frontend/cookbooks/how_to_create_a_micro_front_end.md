# How to create a micro-frontend

You decided to add a new micro-frontend into the `pim-enterprise-dev` or `pim-community-dev` codebase? This guide is for you!

Before to start, you need to know the following things:
- The new micro-frontend location (in this cookbook it will be named $MICRO_FRONTEND_PATH)
- The name of your micro-frontend ($MICRO_FRONTEND_NAME)

## Register your new micro-frontend
First step is to register the new package as a workspace of the PIM, we do this in order to mutualize the `node_modules`.

To do this, you need to modify the following `package.json` depending on where your micro-frontend is located:

- In the Community Edition:
  - std-build/package.json
  - package.json

- In the Trial Edition:
  - tria/package.json

- In the Growth Edition:
  - std-build/package.json
  - package.json
  - grth/package.json

- In the Enterprise Edition:
  - std-build/package.json
  - package.json
  - tria/package.json
  - grth/package.json
 
 
In those files you need to add your package path into the workspaces:
```diff
  "workspaces" : {
+   "$RELATIVE_PATH_TO_PROJECT_CONTAINING_MICRO_FRONTEND/$MICRO_FRONTEND_PATH/$MICRO_FRONTEND_NAME",
  }
```

For example, the measurement micro-frontend present in the community edition we have the following workspace:
```
  // In community edition package.json
  "front-packages/measurement",

  // In the others package.json
  "vendor/akeneo/pim-community-dev/front-packages/measurement",
```

## Initialize your project with the custom create react app template
First you need to clone the project `git@github.com:StevenVAIDIE/create-react-app.git` into `~/dev`

Then you need to run the following command in your project:
```
  yarn create react-app $MICRO_FRONTEND_PATH/$MICRO_FRONTEND_NAME --template file:../../your_workspace/create-react-app/packages/cra-template-typescript
```

The script will create all the things you need to develop a micro-frontend in the PIM.

To test it you can launch the following command in `$MICRO_FRONTEND_PATH` directory: `yarn app:start` or `docker-compose run --rm -p 3000:3000 node yarn workspace $MICRO_FRONTEND_NAME app:start`.

## Replace the package name
Unfortunately it's not possible to create a scoped package with create-react-app (for example: @pim-community/measurement).
So you need to modify the package.json file of your new micro-frontend.

```diff
-     "name": "$PROJECT_NAME",
+     "name": "@akeneo-pim-community/$PROJECT_NAME",
```

Note:
Replace `akeneo-pim-community` by `akeneo-pim-enterprise` if your micro-frontend is in enterprise edition.

## Add your test suite into the package.json
Now you need to create the following new script into the package.json of the community or enterprise edition (depending on where the micro-frontend is located)
```json
    "$PROJECT_NAME:lint:check": "yarn workspace @akeneo-pim-community/$PROJECT_NAME lint:check",
    "$PROJECT_NAME:build": "yarn workspace @akeneo-pim-community/$PROJECT_NAME lib:build",
    "$PROJECT_NAME:unit": "yarn workspace @akeneo-pim-community/$PROJECT_NAME test:unit:run",
```

And call it in the following scripts: `packages:build`, `packages:unit` and `packages:lint:check`

## Modify the .circleci files to add your package into the cache

Now that the CI launches the tests, you need to add your new micro-frontend to the list of cached micro-frontends. We do that because generating libs of frontend take time.

First you need to add your package into the `Create hash for front packages` step.
```sh
    find $RELATIVE_PATH_TO_PROJECT_CONTAINING_MICRO_FRONTEND/$PROJECT_NAME -type f -print0 | sort -z | xargs -0 sha1sum | sha1sum > ~/$PROJECT_NAME.hash
```

edit this line too, adding the hash file for your micro-frontend:
```sh
    echo "$(date +%F) << parameters.path_to_front_packages >>" | tee -a ~/akeneo-design-system.hash ~/measurement.hash ~/shared.hash ~/catalog-volume-monitoring.hash ~/process-tracker.hash ~/$PROJECT_NAME.hash
```

Then add restore cache step just after the other restore_cache micro-frontend:
```yaml
      - restore_cache:
            name: Restore micro-frontend $PROJECT_NAME cache
            key: micro-frontend-$PROJECT_NAME-{{ checksum "~/$PROJECT_NAME.hash" }}
```

And save in cache the micro-frontend generated lib with the following lines:
```yaml
      - save_cache:
            name: Save micro-frontend $PROJECT_NAME cache
            key: micro-frontend-$PROJECT_NAME-{{ checksum "~/$PROJECT_NAME.hash" }}
            paths:
                - $RELATIVE_PATH_TO_PROJECT_CONTAINING_MICRO_FRONTEND/$PROJECT_NAME
```

## Add your micro-frontend in the .prettierignore

Add this line in the `.prettierignore`:
```
  $MICRO_FRONTEND_PATH/$MICRO_FRONTEND_NAME/**/*
```
