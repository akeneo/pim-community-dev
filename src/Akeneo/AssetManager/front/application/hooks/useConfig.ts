import {getValueConfig} from '../configuration/value';

const useConfig = (key: string) => {
  switch (key) {
    case 'value':
      return getValueConfig();
    default:
      throw 'Invalid config key';
  }
};

export {useConfig};
