jest.unmock('./useOperatorTranslator');

import {renderHook} from '@testing-library/react-hooks';
import {useOperatorTranslator} from './useOperatorTranslator';
import {Operator} from '../models/Operator';

test('it translates an operator with first letter uppercase', () => {
    const {result} = renderHook(() => useOperatorTranslator());

    expect(result.current(Operator.IS_EMPTY)).toEqual('Akeneo_catalogs.product_selection.operators.EMPTY');
});
