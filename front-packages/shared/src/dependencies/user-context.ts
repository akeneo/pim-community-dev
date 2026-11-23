import {UserContext} from '../DependenciesProvider.type';
import {getJson} from './http';
import {Events, withEvents} from './events';

const USER_CONTEXT_URL = '/rest/user/';

type ContextData = {[key: string]: any};

type ApplicationUserContext = UserContext &
  Events & {
    initialize: () => Promise<void>;
    refresh: () => Promise<void>;
    get: (key: string) => any;
    set: (key: string, value: any, options?: {silent?: boolean}) => void;
  };

let contextData: ContextData = {};

const load = async (): Promise<void> => {
  const response = await getJson<ContextData>(USER_CONTEXT_URL);

  contextData = {
    ...response,
    uiLocale: response.user_default_locale,
    catalogLocale: response.catalog_default_locale,
    catalogScope: response.catalog_default_scope,
  };
};

/**
 * Current user data (locales, scope, preferences...), historically `pim/user-context`.
 * It triggers a "change:<key>" event each time a key is updated (unless silent).
 */
const userContext: ApplicationUserContext = withEvents({
  initialize: load,
  refresh: load,
  get: (key: string) => contextData[key],
  set(key: string, value: any, options: {silent?: boolean} = {}) {
    contextData[key] = value;

    if (!options.silent) {
      userContext.trigger(`change:${key}`);
    }
  },
});

export {userContext};
export type {ApplicationUserContext};
