import { configureStore } from '@reduxjs/toolkit';
import todosReducer from './reducer';

const store = configureStore({
    reducer: todosReducer,
});

export default store;
