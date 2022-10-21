import {renderHook} from '@testing-library/react-hooks';
import {useSaveIdentifierGenerator} from '../';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {IdentifierGenerator} from '../../models/';
import {Violation} from '../../validators/Violation';
import {act} from '@testing-library/react';
import reactRouterDom from 'react-router-dom';

jest.mock('react-router-dom');

const identifierGenerator: IdentifierGenerator = {
  code: 'initialCode',
  labels: {
    en_US: 'Initial Label',
  },
  conditions: [],
  structure: [],
  delimiter: null,
  target: 'sku',
};

type OnSaveIdentifierProps = {
  onSave: (identifierGenerator: IdentifierGenerator) => void;
  validationErrors: Violation[];
};

describe('useSaveIdentifierGenerator', () => {
  test('it saves an identifier generator', () => {
    const pushMock = jest.fn();
    reactRouterDom.useHistory = jest.fn().mockReturnValue({push: pushMock});

    jest.spyOn(global, 'fetch').mockResolvedValue({
      status: 201,
    } as Response);
    const {result, waitFor} = renderHook<null, OnSaveIdentifierProps>(() => useSaveIdentifierGenerator(), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onSave(identifierGenerator);
    });

    waitFor(() => {
      expect(pushMock).toHaveBeenCalledWith('/initialCode');
    });
  });

  test('it gets validation errors', () => {
    const violationErrors = [{message: 'a message', path: 'a path'}, {message: 'another message'}];
    jest.spyOn(global, 'fetch').mockResolvedValue({
      status: 400,
      json: () => Promise.resolve(violationErrors),
    } as Response);
    const {result, waitFor} = renderHook<null, OnSaveIdentifierProps>(() => useSaveIdentifierGenerator(), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onSave(identifierGenerator);
    });

    waitFor(() => expect(result.current.validationErrors).toEqual(violationErrors));
  });

  test('it manages 500', () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      status: 500,
    } as Response);
    const {result} = renderHook<null, OnSaveIdentifierProps>(() => useSaveIdentifierGenerator(), {
      wrapper: createWrapper(),
    });

    act(() => {
      result.current.onSave(identifierGenerator);
    });
  });
});
