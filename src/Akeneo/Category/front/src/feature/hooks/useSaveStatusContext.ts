import {useContext} from 'react';
import {SaveStatusContext} from '../components/providers/SaveStatusProvider';

export const useSaveStatusContext = () => {
  const context = useContext(SaveStatusContext);
  if (null === context) {
    throw new Error();
  }

  return context;
};
