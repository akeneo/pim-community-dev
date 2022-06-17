import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {AttributeDataMapping} from 'feature/models';
import {usePreviewData} from './usePreviewData';
import {act} from '@testing-library/react-hooks';

const dataMapping: AttributeDataMapping = {
  uuid: '3bf78ab4-30b8-415b-8545-bb6d273c37e7',
  target: {
    code: 'description',
    type: 'attribute',
    attribute_type: 'pim_catalog_textarea',
    channel: null,
    locale: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    source_configuration: null,
  },
  sources: ['a9a2db9d-d150-4297-8265-d2e52394979f'],
  operations: [{uuid: 'uuid', type: 'clean_html_tags'}],
  sample_data: ['<b>product_1</b>', 'product_2', null],
};

const flushPromises = () => new Promise(setImmediate);

test('It returns the preview data', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () =>
      Promise.resolve({
        preview_data: ['product1', 'product2', null],
      }),
  }));

  const {result} = renderHookWithProviders(() => usePreviewData(dataMapping));
  const [isLoading, previewData, hasError] = result.current;
  expect(isLoading).toBe(true);
  expect(previewData).toEqual([]);
  expect(hasError).toBe(false);

  await act(async () => {
    await flushPromises();
  });

  const [isLoadingAfterFlushPromise, previewDataAfterFlushPromise, hasErrorAfterFlushPromise] = result.current;
  expect(isLoadingAfterFlushPromise).toBe(false);
  expect(previewDataAfterFlushPromise).toEqual(['product1', 'product2', null]);
  expect(hasErrorAfterFlushPromise).toBe(false);

  expect(global.fetch).toBeCalledWith('pimee_tailored_import_generate_preview_data_action', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({
      sample_data: dataMapping.sample_data,
      operations: dataMapping.operations,
      target: dataMapping.target,
    }),
    method: 'POST',
  });
});

test('It returns the error when an error occurred during the generation', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
    json: async () =>
      Promise.resolve([
        {
          messageTemplate: 'error.key.an_error',
          invalidValue: '',
          message: 'this is an error',
          parameters: {},
          propertyPath: '',
        },
        {
          messageTemplate: 'error.key.another_error',
          invalidValue: '',
          message: 'this is another error',
          parameters: {},
          propertyPath: '',
        },
      ]),
  }));

  const {result} = renderHookWithProviders(() => usePreviewData(dataMapping));
  await act(async () => {
    await flushPromises();
  });

  const [isLoading, previewData, hasError] = result.current;
  expect(isLoading).toBe(false);
  expect(previewData).toEqual([]);
  expect(hasError).toBe(true);

  expect(global.fetch).toBeCalledWith('pimee_tailored_import_generate_preview_data_action', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({
      sample_data: dataMapping.sample_data,
      operations: dataMapping.operations,
      target: dataMapping.target,
    }),
    method: 'POST',
  });
});

test('It does not call fetch when there is no sample data', async () => {
  const dataMappingWithoutSampleData = {
    ...dataMapping,
    sample_data: [],
  };

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () =>
      Promise.resolve({
        preview_data: ['product1', 'product2', null],
      }),
  }));

  const {result} = renderHookWithProviders(() => usePreviewData(dataMappingWithoutSampleData));

  const [isLoading, previewData, hasError] = result.current;
  expect(isLoading).toBe(false);
  expect(previewData).toEqual([]);
  expect(hasError).toBe(false);

  expect(global.fetch).not.toBeCalled();
});

test('It does not call fetch when there is no operations', async () => {
  const dataMappingWithoutOperation = {
    ...dataMapping,
    operations: [],
  };

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () =>
      Promise.resolve({
        preview_data: ['product1', 'product2', null],
      }),
  }));

  const {result} = renderHookWithProviders(() => usePreviewData(dataMappingWithoutOperation));

  const [isLoading, previewData, hasError] = result.current;
  expect(isLoading).toBe(false);
  expect(previewData).toEqual([]);
  expect(hasError).toBe(false);

  expect(global.fetch).not.toBeCalled();
});
