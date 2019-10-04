import {Index} from './application/apps/pages/Index';
import {RouterContext} from './application/shared/router/router-context';
import {TranslateContext} from './application/shared/translate/translate-context';
import {composeProviders} from './infrastructure/compose-providers';

const RouterProvider = RouterContext.Provider;
const TranslateProvider = TranslateContext.Provider;

export {Index, composeProviders, RouterProvider, TranslateProvider};
