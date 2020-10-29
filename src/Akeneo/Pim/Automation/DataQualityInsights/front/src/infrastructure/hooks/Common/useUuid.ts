const uuidV5 = require('uuid/v5');

const DEFAULT_UUID_NAMESPACE = 'ee545a8b-3ebc-474a-a085-16fc3335009b';
const DEFAULT_BASE_ID = '';

type Uuid = {
  baseId: string;
  uuidNamespace: string;
  uuid: (key: string) => string;
};

export const useUuid = (baseId: string = DEFAULT_BASE_ID, namespace: string = DEFAULT_UUID_NAMESPACE): Uuid => {
  return {
    baseId: baseId,
    uuidNamespace: namespace,
    uuid: (key: string): string => {
      return uuidV5(`${baseId}${key}`, namespace);
    },
  };
};
