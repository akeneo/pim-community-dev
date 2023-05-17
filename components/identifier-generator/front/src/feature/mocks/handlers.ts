import { rest } from 'msw';
import mockedScopes from '../tests/fixtures/scopes';
import {Operator} from '../models';
import uiLocales from '../tests/fixtures/uiLocales';
import initialGenerator from '../tests/fixtures/initialGenerator';
import mockedIdentifierGenerators from '../tests/fixtures/identifierGenerators';
import {firstPaginatedResponse, secondPaginatedResponse, selectedOptionsMockResponse} from '../tests/fixtures/options';
import {mockedFamiliesPage1, mockedFamiliesPage2, mockedFamiliesSearch} from '../tests/fixtures/families';

export const handlers = [
  rest.get('/akeneo_identifier_generator_get_identifier_attributes', (req, res, ctx) => {
    return res(
      ctx.status(200),
      ctx.json([{code: 'sku', label: 'Sku'}])
    );
  }),
  rest.get('/pim_enrich_attribute_rest_get', (req, res, ctx) => {
    const identifier = req.url.searchParams.get('identifier') || '';
    if (identifier === 'deleted_attribute') {
      return res(ctx.status(404),);
    } else if (identifier === 'unknown_attribute') {
      return res(ctx.status(500));
    } else if (identifier === 'unauthorized_attribute') {
      return res(ctx.status(401));
    }
    return res(
      ctx.status(200),
      ctx.json({
        code: identifier,
        labels: {en_US: 'Simple select', fr_FR: 'Select simple'},
        localizable: identifier.includes('localizable'),
        scopable: identifier.includes('scopable'),
      })
    );
  }),
  rest.get('/pim_enrich_channel_rest_index', (req, res, ctx) => {
    return res(
      ctx.status(200),
      ctx.json(mockedScopes)
    );
  }),
  rest.get('/akeneo_identifier_generator_nomenclature_rest_get', (req, res, ctx) => {
    return res(
      ctx.status(200),
      ctx.json({
        propertyCode: 'family',
        operator: Operator.EQUALS,
        value: 3,
        generate_if_empty: true,
        values: {
          Family1: 'FA1',
          Family2: 'FA2',
        },
      })
    );
  }),
  rest.get('/akeneo_identifier_generator_get_families', (req, res, ctx) => {
    const codes = req.url.searchParams.getAll('codes[]');
    const page = parseInt(req.url.searchParams.get('page') || '');
    const search = req.url.searchParams.get('search');

    if (codes.includes('unauthorized')) {
      return res(ctx.status(403));
    } else if (codes.includes('unknown')) {
      return res(ctx.status(500));
    } else if (search === 'My Family') {
      return res(
        ctx.status(200),
        ctx.json(mockedFamiliesSearch)
      );
    } else if (page === 2) {
      return res(
        ctx.status(200),
        ctx.json(mockedFamiliesPage2)
      );
    }
    return res(
      ctx.status(200),
      ctx.json(mockedFamiliesPage1)
    );
  }),
  rest.patch('/akeneo_identifier_generator_nomenclature_rest_update', async (req, res, ctx) => {
    const {value} = await req.json();
    if (value === null) {
      return res(
        ctx.status(400),
        ctx.json([
          {path: 'value', message: 'Error on value'},
          {path: 'operator', message: 'Error on operator'},
        ])
      );
    }
    return res(
      ctx.status(200),
      ctx.json([])
    );
  }),
  rest.get('/akeneo_identifier_generator_get_attribute_options', (req, res, ctx) => {
    const codes = req.url.searchParams.getAll('codes[]');
    const page = parseInt(req.url.searchParams.get('page') || '');
    const limit = req.url.searchParams.get('limit') || '';
    const search = req.url.searchParams.get('search') || '';

    if (codes?.includes('error_response')) {
      return res(ctx.status(500));
    }
    if (codes?.length === 3 && parseInt(limit) === 3) {
      return res(ctx.status(200), ctx.json(selectedOptionsMockResponse));
    } else if (page === 2) {
      return res(ctx.status(200), ctx.json(secondPaginatedResponse));
    } else if (search === 'OptionF') {
      return res(ctx.status(200), ctx.json([{code: 'option_f', labels: {en_US: 'OptionF'}}]));
    } else {
      return res(ctx.status(200), ctx.json(firstPaginatedResponse));
    }
  }),
  rest.get('/pim_localization_locale_index', (req, res, ctx) => {
    return res(ctx.status(200), ctx.json(uiLocales));
  }),
  rest.patch('/akeneo_identifier_generator_rest_update', async (req, res, ctx) => {
    const {code} = await req.json();
    if (code.includes(' ')) {
      return res(ctx.status(500), ctx.json([
        {
          message: 'Association type code may contain only letters, numbers and underscores',
          path: 'code',
        },
      ]));
    }
    return res(ctx.status(200), ctx.json(initialGenerator));
  }),
  rest.get('/akeneo_identifier_generator_rest_list', (req, res, ctx) => {
    return res(ctx.status(200), ctx.json(mockedIdentifierGenerators));
  }),
  rest.delete('/akeneo_identifier_generator_rest_delete', (req, res, ctx) => {
    const code = req.url.searchParams.get('code') || '';
    if (code === 'error') {
      return res(ctx.status(500));
    }
    return res(ctx.status(200));
  }),
  rest.post('/akeneo_identifier_generator_rest_create', async (req, res, ctx) => {
    const {code} = await req.json();
    if (code.includes('validation-error')) return res(ctx.status(400), ctx.json([{message: 'a message', path: 'a path'}, {message: 'another message'}]));
    if (code.includes('back-error')) return res(ctx.status(500));
    return res(ctx.status(201), ctx.json(initialGenerator));
  }),
  rest.get('/akeneo_identifier_generator_rest_get', (req, res, ctx) => {
    return res(ctx.status(200), ctx.json(initialGenerator));
  }),
  rest.get('pim_enrich_categorytree_children', (req, res, ctx) => {
    return res(ctx.status(200), ctx.json({
      attr: {
        id: '69',
        'data-code': 'subCategory',
      },
      children: [],
      data: 'Sub category',
      state: 'open leaf',
    }));
  }),
  rest.get('akeneo_identifier_generator_get_category_labels', (req, res, ctx) => {
    return res(ctx.status(200), ctx.json({
      categoryCode1: 'Category code 1',
      categoryCode2: 'Category code 2',
    }));
  }),
];

