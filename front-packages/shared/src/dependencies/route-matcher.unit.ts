import {matchRoute} from './route-matcher';

const routes = {
  pim_enrich_product_index: {tokens: [['text', '/enrich/product/']], defaults: {}, requirements: {}},
  pim_enrich_product_edit: {
    tokens: [
      ['variable', '/', '[^/]++', 'uuid'],
      ['text', '/enrich/product'],
    ],
    defaults: {},
    requirements: {},
  },
  pim_enrich_family_index: {
    tokens: [
      ['variable', '/', '\\d+', 'page'],
      ['text', '/configuration/family'],
    ],
    defaults: {page: 1},
    requirements: {_method: 'GET'},
  },
  pim_enrich_family_delete: {
    tokens: [['text', '/configuration/family/delete']],
    defaults: {},
    requirements: {_method: 'DELETE'},
  },
};

test('it matches a static route', () => {
  expect(matchRoute(routes, '#/enrich/product/')).toEqual({
    name: 'pim_enrich_product_index',
    route: routes.pim_enrich_product_index,
    params: {},
  });
});

test('it matches a route with variables, ignoring the query string and the fragment', () => {
  const match = matchRoute(routes, 'enrich/product/1234?dataLocale=en_US#tab');

  expect(match && match.name).toBe('pim_enrich_product_edit');
  expect(match && match.params).toEqual({uuid: '1234'});
  expect(matchRoute(routes, 'enrich/product/1234#tab')).toMatchObject({params: {uuid: '1234'}});
});

test('it uses the default value of optional variables', () => {
  expect(matchRoute(routes, '/configuration/family')).toMatchObject({params: {page: 1}});
  expect(matchRoute(routes, '/configuration/family/3')).toMatchObject({params: {page: '3'}});
});

test('it removes the base URL', () => {
  expect(matchRoute(routes, '/app_dev.php/enrich/product/', '/app_dev.php')).toMatchObject({
    name: 'pim_enrich_product_index',
  });
});

test('it ignores routes that are not reachable with GET or POST and returns false without match', () => {
  expect(matchRoute(routes, '/configuration/family/delete')).toBe(false);
  expect(matchRoute(routes, '/unknown')).toBe(false);
});
