import * as trans from 'pimui/lib/translator';

const __ = (key: string, placeholders: any = {}, number: number = 1) => {
  const translation = trans.get(key, {...placeholders}, number);

  return undefined === translation ? key : translation;
};

export {__};
