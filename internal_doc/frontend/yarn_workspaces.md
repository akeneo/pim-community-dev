# Yarn workspaces

See the official documentation on [Yarn workspace](https://yarnpkg.com/lang/en/docs/workspaces/) for a detailed explanation.

In short, Yarn workspaces are used for monorepos and allow us to have multiple independent frontend packages inside the PIM (each one with its own dependencies and scripts).

## Useful for the Enterprise edition

Since the Community Edition is declared as a Yarn workspace for the Enterprise Edition we don't need to duplicate dependencies between them anymore.

`package.json`

```json
"workspaces": [
    "vendor/akeneo/pim-community-dev"
]
```

Yarn will treat the Community Edition like a normal dependency and install its required libs.

# How to use them

Create a new folder with a `package.json`:

```json
{
  "name": "@akeneo-pim-community/connectivity-connection",
  "version": "1.0.0",
  "main": "src/index.ts",
  "private": true
}
```

Add workspace to the root `package.json` (CE or EE) with the relative path to your project folder:

```json
{
  "workspaces": ["src/Akeneo/Connectivity/Connection/front"]
}
```

Workspaces are symlinked by Yarn into the `node_modules` folder, so you can use them as standard dependencies with import in other parts of the project (and have a working auto-completion and Intellisense):

```js
import { Settings } from "@akeneo-pim-community/connectivity-connection";
```
