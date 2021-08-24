import { createReducer } from '@reduxjs/toolkit';
import {Connection} from '../model/connection';

export type connectionState = {
    connection: Connection|null,
}

const initialState = {
    connection: null,
};

const todosReducer = createReducer<connectionState>(initialState, (builder) => {
});

export default todosReducer;
