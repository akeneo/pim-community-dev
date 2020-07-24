import React from 'react';

import {
    AttributeOptionsExtraData,
    ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
    REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
    attributeOptionsExtraDataReducer
} from "akeneopimstructure/js/attribute-option/reducers/attributeOptionsExtraDataReducer";

const givenInitialState = ():AttributeOptionsExtraData => {
    return {
        red: <div>TEST RED</div>,
        blue: <div>TEST BLUE</div>,
        yellow: <div>TEST YELLOW</div>,
    };
};

describe('attributeOptionsExtraDataReducer', () => {
    it('should add extra data in state when the action ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION is received', () => {
        let state;
        let result;

        state = givenInitialState();
        result = attributeOptionsExtraDataReducer(state, {
            type: ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
            code: 'black',
            extra: <div>TEST BLACK</div>
        });

        expect(result).toEqual({
            red: <div>TEST RED</div>,
            blue: <div>TEST BLUE</div>,
            yellow: <div>TEST YELLOW</div>,
            black: <div>TEST BLACK</div>,
        });

        state = givenInitialState();
        result = attributeOptionsExtraDataReducer(state, {
            type: ADD_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
            code: 'red',
            extra: <div>NEW RED</div>
        });

        expect(result).toEqual({
            red: <div>NEW RED</div>,
            blue: <div>TEST BLUE</div>,
            yellow: <div>TEST YELLOW</div>,
        });
    });

    it('should remove extra data from state when the action REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION is received', () => {
        let result;
        let state;

        state = {};
        result = attributeOptionsExtraDataReducer(state, {
            type: REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
            code: 'black',
            extra: <div>TEST BLACK</div>
        });

        expect(result).toEqual({});

        state = givenInitialState();
        result = attributeOptionsExtraDataReducer(state, {
            type: REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
            code: 'black',
            extra: <div>TEST BLACK</div>
        });

        expect(result).toEqual({
            red: <div>TEST RED</div>,
            blue: <div>TEST BLUE</div>,
            yellow: <div>TEST YELLOW</div>,
        });

        state = givenInitialState();
        result = attributeOptionsExtraDataReducer(state, {
            type: REMOVE_ATTRIBUTE_OPTION_EXTRA_DATA_ACTION,
            code: 'red',
            extra: <div>TEST RED</div>
        });

        expect(result).toEqual({
            blue: <div>TEST BLUE</div>,
            yellow: <div>TEST YELLOW</div>,
        });
    });

    it('should not update the state when an unknown action is received', () => {
        let result;
        let state;

        state = {};
        result = attributeOptionsExtraDataReducer(state, {
            type: 'unknown_action',
            code: 'black',
            extra: <div>TEST BLACK</div>
        });

        expect(result).toEqual({});

        state = givenInitialState();
        result = attributeOptionsExtraDataReducer(state, {
            type: 'unknown_action',
            code: 'black',
            extra: <div>TEST BLACK</div>
        });

        expect(result).toEqual({
            red: <div>TEST RED</div>,
            blue: <div>TEST BLUE</div>,
            yellow: <div>TEST YELLOW</div>
        });
    });
});
