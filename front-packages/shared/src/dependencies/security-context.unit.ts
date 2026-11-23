require('jest-fetch-mock').enableMocks();
import fetchMock from 'jest-fetch-mock';
import {securityContext} from './security-context';

test('it loads the ACLs of the current user', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({pim_enrich_product_index: true, pim_enrich_product_edit: false}));

  expect(await securityContext.initialize()).toEqual({pim_enrich_product_index: true, pim_enrich_product_edit: false});
  expect(fetchMock.mock.calls[0][0]).toBe('/rest/security/');
  expect(securityContext.isGranted('pim_enrich_product_index')).toBe(true);
  expect(securityContext.isGranted('pim_enrich_product_edit')).toBe(false);
  expect(securityContext.isGranted('unknown')).toBe(false);
  expect(securityContext.get('pim_enrich_product_edit')).toBe(false);
});
