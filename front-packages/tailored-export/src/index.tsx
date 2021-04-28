import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {ColumnsTab, ColumnsConfiguration} from './feature';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';

const columnsConfiguration: ColumnsConfiguration = [
  {
    uuid: 'test_uuid_1',
    target: 'name',
    sources: [
      {
        uuid: '0001',
        code: 'name',
        locale: null,
        channel: null,
        operations: [
          {
            type: 'default_value',
            value: 'foo',
          },
          {
            type: 'replace',
            mapping: {
              Bag: 'sac a pied',
            },
          },
        ],
        selection: {
          type: 'code',
        },
      },
      {
        uuid: '0002',
        code: 'name',
        locale: null,
        channel: null,
        operations: [
          {
            type: 'default_value',
            value: 'toto',
          },
        ],
        selection: {
          type: 'code',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          value: '0001',
        },
        {
          type: 'source',
          value: '0002',
        },
        {
          type: 'string',
          value: 'cm',
        },
      ],
    },
  },
  {
    uuid: 'test_uuid_2',
    target: 'collection',
    sources: [
      {
        uuid: '0001',
        code: 'collection',
        locale: null,
        channel: null,
        operations: [
          {
            type: 'replace',
            mapping: {
              spring_2015: 'printemps 2015 yeaaah',
              summer_2017: 'ete 2020',
            },
          },
        ],
        selection: {
          type: 'label',
          locale: 'fr_FR',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          value: '0001',
        },
      ],
    },
  },
  {
    uuid: 'test_uuid_3',
    target: 'weight',
    sources: [
      {
        uuid: '0001',
        code: 'weight',
        locale: null,
        channel: null,
        operations: [
          {
            type: 'default_value',
            value: 'toto',
          },
          {
            type: 'convert',
            unit: 'MILLIGRAM',
          },
        ],
        selection: {
          type: 'amount',
        },
      },
      {
        uuid: '0002',
        code: 'weight',
        locale: null,
        channel: null,
        operations: [
          {
            type: 'convert',
            unit: 'MILLIGRAM',
          },
        ],
        selection: {
          type: 'label',
          locale: 'fr_FR',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          value: '0001',
        },
        {
          type: 'string',
          value: ' ',
        },
        {
          type: 'source',
          value: '0002',
        },
      ],
    },
  },
  {
    uuid: 'test_uuid_4',
    target: 'weight-customized',
    sources: [
      {
        uuid: '0001',
        code: 'weight',
        locale: null,
        channel: null,
        operations: [
          {
            type: 'convert',
            unit: 'MILLIGRAM',
          },
        ],
        selection: {
          type: 'amount',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'source',
          value: '0001',
        },
      ],
    },
  },
  {
    uuid: 'test_uuid_5',
    target: 'weight-unit-customized',
    sources: [],
    format: {
      type: 'concat',
      elements: [
        {
          type: 'string',
          value: 'MILLIGRAM',
        },
      ],
    },
  },
];

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
        <ColumnsTab columnsConfiguration={columnsConfiguration} />
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
