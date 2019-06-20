import * as trans from 'pimui/lib/translator';

export default (key: string, placeholders: any = {}, number: number = 1) => {
  return trans.get(key, {...placeholders}, number);
};
