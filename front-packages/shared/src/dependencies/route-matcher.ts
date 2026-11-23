/**
 * Matches a URL against the Symfony routes exposed by FOSJsRoutingBundle (historically `pim/route-matcher`).
 */
type RouteToken = string[];

type RouteDefinition = {
  tokens: RouteToken[];
  defaults: {[name: string]: any};
  requirements: {[name: string]: any};
};

type MatchedRoute = {
  name: string;
  route: RouteDefinition;
  params: {[name: string]: any};
};

const escape = (value: string) => value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');

const normalizeUrl = (url: string, baseUrl: string): string => {
  let normalized = url;

  if (normalized.indexOf('?') !== -1) {
    normalized = normalized.substring(0, normalized.indexOf('?'));
  }
  if (normalized.indexOf('#') === 0) {
    normalized = normalized.substring(1);
  }
  if (normalized.indexOf('#') !== -1) {
    normalized = normalized.substring(0, normalized.indexOf('#'));
  }
  if (normalized.indexOf('/') !== 0) {
    normalized = `/${normalized}`;
  }

  return normalized.replace(baseUrl, '');
};

const isReachableWithGetOrPost = (route: RouteDefinition) => {
  const methods = route.requirements._method;

  return !methods || methods.indexOf('GET') !== -1 || methods.indexOf('POST') !== -1;
};

const matchRoute = (
  routes: {[name: string]: RouteDefinition},
  url: string,
  baseUrl: string = ''
): MatchedRoute | false => {
  const path = normalizeUrl(url, baseUrl);

  for (const name in routes) {
    const route = routes[name];
    if (!isReachableWithGetOrPost(route)) {
      continue;
    }

    let pattern = '';
    let matching = true;
    const variables: string[] = [];

    route.tokens.forEach(token => {
      if ('text' === token[0]) {
        pattern = escape(token[1]) + pattern;
        matching = matching && path.indexOf(token[1]) !== -1;
      } else if ('variable' === token[0]) {
        const separator = escape(token[1]);
        const variablePattern = token[2].replace('++', '+');
        pattern =
          undefined === route.defaults[token[3]]
            ? `${separator}(${variablePattern})${pattern}`
            : `(?:${separator}(${variablePattern}))?${pattern}`;
        variables.push(token[3]);
      }
    });

    if (!matching) {
      continue;
    }

    const matches = path.match(new RegExp(`^${pattern}$`));
    if (matches) {
      const params: {[name: string]: any} = {};
      variables.reverse().forEach((variable, index) => {
        const matchedValue = matches[index + 1];
        params[variable] = undefined !== matchedValue ? matchedValue : route.defaults[variable];
      });

      return {name, route, params};
    }
  }

  return false;
};

export {matchRoute};
export type {MatchedRoute, RouteDefinition};
