import {renderHook} from '@testing-library/react-hooks';
import {useSaveGenerator} from '../useSaveGenerator';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {NotificationLevel} from '@akeneo-pim-community/shared';
import {act} from '@testing-library/react';

const mockNotify = jest.fn();

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => key,
  useRouter: () => {
    return {
      generate: (key: string) => key,
    };
  },
  useNotify: () => {
    return mockNotify;
  },
}));

describe('useSaveGenerator', () => {
  it('should save a generator without failure', async () => {
    const generator = {
      code: 'code',
      target: 'sku',
      structure: [],
      delimiter: '-',
      labels: {'en_US': 'My Generator'},
      conditions: []
    };
    const {result, waitFor} = renderHook(() => useSaveGenerator(), {wrapper: createWrapper()});
    await waitFor(() => {
      return !!result?.current?.save;
    });
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(generator),
    } as Response);

    act(() => {
      result?.current?.save(generator);
    });

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });

    expect(mockNotify).toHaveBeenCalled();
    expect(mockNotify).toHaveBeenCalledWith(
      NotificationLevel.SUCCESS,
      'pim_identifier_generator.flash.update.success'
    );
  });

  it('should handle errors on save', async () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
    const generator = {
      code: 'code',
      target: 'sku',
      structure: [],
      delimiter: '-',
      labels: {'en_US': 'My Generator'},
      conditions: []
    };
    const {result, waitFor} = renderHook(() => useSaveGenerator(), {wrapper: createWrapper()});
    await waitFor(() => {
      return !!result?.current?.save;
    });
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      json: () => Promise.resolve([
        {
          message: 'Association type code may contain only letters, numbers and underscores',
          path: 'code'
        }
      ]),
    } as Response);

    act(() => {
      result?.current?.save(generator);
    });
    // it should show a toast
    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });

    expect(mockNotify).toHaveBeenCalled();
    expect(mockNotify).toHaveBeenCalledWith(
      NotificationLevel.ERROR,
      'pim_identifier_generator.flash.create.error'
    );
    expect(result.current.error).toEqual([
      {
        message: 'Association type code may contain only letters, numbers and underscores',
        path: 'code'
      }
    ]);
  });
});
