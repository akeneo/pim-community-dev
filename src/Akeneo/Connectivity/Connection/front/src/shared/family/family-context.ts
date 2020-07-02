import {createContext} from 'react';
import {Family} from './use-family';

export const FamilyContext = createContext<Family[] | undefined>(undefined);
