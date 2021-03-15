# Frontend developer toolkit

## Tooling

Our front is running with [NodeJS](https://nodejs.org/en/), built with [Webpack](https://webpack.js.org/concepts/) and
the dependencies are handled by [Yarn](https://yarnpkg.com/) (and not mpn).
In order to keep a good code quality we use [ESLint](https://eslint.org/) and the code is formatting with [Prettier](https://prettier.io/).

## Common commands

Here is the most common commands you will use during your front developments:

- `make front` build all the front-end parts. You use it to boot the front.
- `make assets` clean and reinstall assets.
- `make javascript-dev` clean and run webpack to build the front. Use it when you don't see your translations, routing or form extensions.
- `docker-compose run -u node --rm node yarn run webpack-watch` launch the watch mode from webpack. Use it when you dev in the front. It builds the front part you change in real-time.
- `docker-compose run -u node --rm node yarn lint` Check the code quality and formatting rules with Prettier and ESLint.
- `docker-compose run -u node --rm node yarn lint-fix` Fix the code quality and formatting rules with Prettier and ESLint.

To go deeper in front commands, you can explore the `scripts` part of the `/package.json` file, and you can run `make` in you command line see available make commands.

## Recommended Browser extensions

- React DevTools
  https://github.com/facebook/react/tree/master/packages/react-devtools-extensions

- Redux DevTools
  https://github.com/zalmoxisus/redux-devtools-extension

## Recommended IDE extensions

In PHPStorm, you can enable ESLint by going into `File > Settings > Languages & Frameworks > Javascript > Code Quality Tools > ESLint` and then click on `Automatic ESLint configuration`.
