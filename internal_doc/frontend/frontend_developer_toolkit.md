# Frontend developer toolkit

## Tooling

The frontend is built with [NodeJS](https://nodejs.org/) and [Webpack](https://webpack.js.org/) and
the dependencies are handled by [Yarn](https://yarnpkg.com/) (not NPM).

In order to keep a good code quality we use [ESLint](https://eslint.org/) and the code is formatted with [Prettier](https://prettier.io/).

## Common commands

Here is the most common commands you will use during your front developments:

- `make front` build all the front-end parts. You use it to boot the front.
- `make assets` clean and reinstall assets.
- `make javascript-dev` clean and run webpack to build the front. Use it when you don't see your translations, routing or form extensions.
- `docker-compose run -u node --rm node yarn run webpack-watch` launch the watch mode from webpack. Use it when you dev in the front. It builds the front part you change in real-time.
- `docker-compose run -u node --rm node yarn lint` Check the code quality and formatting rules with Prettier and ESLint.
- `docker-compose run -u node --rm node yarn lint-fix` Fix the code quality and formatting rules with Prettier and ESLint.

To see all the remaining frontend commands you can explore the `scripts` part of the `package.json` file.

You can also run `make` in your terminal to display a list of all the commands available in the PIM (frontend & backend).

## Recommended Browser extensions

- React DevTools
  https://github.com/facebook/react/tree/master/packages/react-devtools-extensions

- Redux DevTools
  https://github.com/zalmoxisus/redux-devtools-extension

## Recommended IDE extensions

In PHPStorm, you can enable ESLint by going into `File > Settings > Languages & Frameworks > Javascript > Code Quality Tools > ESLint` and then click on `Automatic ESLint configuration`.
