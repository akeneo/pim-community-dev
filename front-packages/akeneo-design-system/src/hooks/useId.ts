import {useState} from 'react';
import {uuid} from '../shared';

const useId = (prefix = ''): string => {
  const [id] = useState<string>(`${prefix}${uuid()}`);

  return id;
};

export {useId};
