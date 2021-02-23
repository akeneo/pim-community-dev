import {renderHook, act} from '@testing-library/react-hooks';
import {Updater} from 'akeneoassetmanager/application/component/library/MassEdit/model/updater';
import {useUpdaterCollection} from './useUpdaterCollection';

test('It can generate a basic selection', () => {
  const {result} = renderHook(() => useUpdaterCollection());

  let [updaterCollection, addUpdater, removeUpdater, setUpdater, alreadyUsedAttributeIdentifiers] = result.current;

  expect(updaterCollection).toEqual([]);
  expect(alreadyUsedAttributeIdentifiers).toEqual([]);

  void act(() => {
    addUpdater(
      {
        identifier: 'description_uuid',
        code: 'description',
        value_per_channel: false,
        value_per_locale: false,
        labels: {en_US: 'Description'},
        is_read_only: false,
        type: 'text',
        order: 0,
        is_required: false,
        asset_family_identifier: 'packshot',
      },
      {channel: 'ecommerce', locale: 'en_US'}
    );
  });

  [updaterCollection, addUpdater, removeUpdater, setUpdater, alreadyUsedAttributeIdentifiers] = result.current;

  const defaultUpdater: Updater = {
    action: 'replace',
    attribute: {
      asset_family_identifier: 'packshot',
      code: 'description',
      identifier: 'description_uuid',
      is_read_only: false,
      is_required: false,
      labels: {
        en_US: 'Description',
      },
      order: 0,
      type: 'text',
      value_per_channel: false,
      value_per_locale: false,
    },
    channel: null,
    data: null,
    locale: null,
    id: updaterCollection[0].id,
  };

  expect(updaterCollection).toEqual([defaultUpdater]);

  expect(alreadyUsedAttributeIdentifiers).toEqual(['description_uuid']);

  void act(() => {
    setUpdater({...defaultUpdater, data: 'nice'});
  });
  [updaterCollection, addUpdater, removeUpdater, setUpdater, alreadyUsedAttributeIdentifiers] = result.current;

  expect(updaterCollection).toEqual([{...defaultUpdater, data: 'nice'}]);

  void act(() => {
    removeUpdater(defaultUpdater.id);
  });
  [updaterCollection, addUpdater, removeUpdater, setUpdater, alreadyUsedAttributeIdentifiers] = result.current;

  expect(updaterCollection).toEqual([]);
  expect(alreadyUsedAttributeIdentifiers).toEqual([]);
});

test('It uses the context if needed', () => {
  const {result} = renderHook(() => useUpdaterCollection());

  let [updaterCollection, addUpdater, removeUpdater, setUpdater, alreadyUsedAttributeIdentifiers] = result.current;

  expect(updaterCollection).toEqual([]);
  expect(alreadyUsedAttributeIdentifiers).toEqual([]);

  void act(() => {
    addUpdater(
      {
        identifier: 'description_uuid',
        code: 'description',
        value_per_channel: true,
        value_per_locale: true,
        labels: {en_US: 'Description'},
        is_read_only: false,
        type: 'text',
        order: 0,
        is_required: false,
        asset_family_identifier: 'packshot',
      },
      {channel: 'ecommerce', locale: 'en_US'}
    );
  });

  [updaterCollection, addUpdater, removeUpdater, setUpdater, alreadyUsedAttributeIdentifiers] = result.current;

  const defaultUpdater: Updater = {
    action: 'replace',
    attribute: {
      asset_family_identifier: 'packshot',
      code: 'description',
      identifier: 'description_uuid',
      is_read_only: false,
      is_required: false,
      labels: {
        en_US: 'Description',
      },
      order: 0,
      type: 'text',
      value_per_channel: true,
      value_per_locale: true,
    },
    channel: 'ecommerce',
    data: null,
    locale: 'en_US',
    id: updaterCollection[0].id,
  };

  expect(updaterCollection).toEqual([defaultUpdater]);
  expect(alreadyUsedAttributeIdentifiers).toEqual(['description_uuid']);
});
