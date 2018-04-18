import { resolve } from 'path';
import * as cucumber from 'cucumber';
import * as glob from 'glob';

const base = process.cwd();

const stepsDirectory = resolve(base, './tests/front/acceptance/cucumber/step-definitions');
const worldFileDirectory = resolve(base, './tests/front/acceptance/cucumber/world.ts');

require(worldFileDirectory).default.World(cucumber);
glob.sync(`${stepsDirectory}/**/*.ts`).forEach((fileName: string) => require(fileName).default(cucumber));

