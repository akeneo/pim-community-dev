import React from 'react';
import {waitFor} from "@testing-library/react";
import {act} from 'react-test-renderer';
import {renderHook} from "@testing-library/react-hooks";
import {useIdentifierAttributes} from '../useIdentifierAttributes';
import {createWrapper} from "../../tests/hooks/config/createWrapper";

describe('useIdentifierAttributes', () => {
  beforeEach(() => {
    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve([{code: 'sku', label: 'Sku'}]),
    });
  })

  test("it retrieves identifier attribute list", async () => {
    const { result } = renderHook(() => useIdentifierAttributes(), {
      wrapper: createWrapper()
    })

    await waitFor(() => result.current.isSuccess);

    act(() => {
      expect(result.current.data).toBeDefined();
      expect(result.current.data).toEqual([{code: 'coucou', label: 'Sku'}]);
    })
  })
})
