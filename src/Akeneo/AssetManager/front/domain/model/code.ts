import {isString} from 'akeneoassetmanager/domain/model/utils';

type Code = string;

export default Code;

export const denormalizeCode = (code: any): Code => {
  if (!isCode(code)) throw new Error('Code expects a string as parameter to be created');

  return code;
};

export const codesAreEqual = (first: Code, second: Code) => first === second;
export const codeStringValue = (code: Code) => code;
export const isCode = isString;
