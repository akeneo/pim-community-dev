import { SetStateAction, Dispatch } from 'react';
declare const useStorageState: <StateType>(defaultValue: StateType, key: string) => [StateType, Dispatch<SetStateAction<StateType>>];
export { useStorageState };
