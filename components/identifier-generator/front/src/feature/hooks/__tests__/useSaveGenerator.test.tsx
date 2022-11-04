import {renderHook} from '@testing-library/react-hooks';
import {useSaveGenerator} from '../useSaveGenerator';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from '@testing-library/react';
import {NotificationLevel} from '@akeneo-pim-community/shared';
import {useNotify} from '@akeneo-pim-community/shared/src/hooks/useNotify';

jest.mock('@akeneo-pim-community/shared/src/hooks/useNotify');

describe('useSaveGenerator', () => {
  it.only('should save a generator without failure', async () => {
    const notify = jest.fn();
    (useNotify as jest.Mock).mockImplementation(() => notify);

      // @ts-ignore
    const {result, waitFor} = renderHook(() => useSaveGenerator(), {wrapper: createWrapper});

    await waitFor(() => !!result);

    act(() => {
      result.current.save({
        code: 'code',
        target: 'sku',
        structure: [],
        delimiter: '-',
        labels: {'en_US': 'My Generator'},
        conditions: []
      });
    });

    await waitFor(() => {
      expect(useNotify).toHaveBeenCalledWith(
        NotificationLevel.SUCCESS,
        'pim_identifier_generator.flash.update.success'
      );
    });
  });

  it('should handle errors on save', () => {
    // it should show a toast
    // it should return this
    const errors = [
      {
        message: 'Association type code may contain only letters, numbers and underscores',
        path: 'code'
      }
    ];

  });
});
