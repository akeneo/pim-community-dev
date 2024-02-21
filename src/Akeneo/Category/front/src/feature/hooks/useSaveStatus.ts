import {useContext} from 'react';
import {SaveStatusContext} from '../components/providers/SaveStatusProvider';

export const useSaveStatus = () => {
  const context = useContext(SaveStatusContext);
  if (null === context) {
    throw new Error('useSaveStatus must be used within a SaveStatusProvider');
  }

  return context;
};
