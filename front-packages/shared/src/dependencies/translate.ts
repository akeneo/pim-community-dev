import {Translate} from '../DependenciesProvider.type';
// @ts-ignore
import Translator from '../../legacy/translator';

/**
 * Wraps the Bazinga Translator to use the Symfony "{{ placeholder }}" syntax and to report missing translations in
 * debug mode (historically `oro/translator`).
 */
const dictionary: {[id: string]: true} = {};
let debug = false;

const originalAdd = Translator.add;
const originalGet = Translator.get;
const originalFromJSON = Translator.fromJSON;

Translator.placeHolderPrefix = '{{ ';
Translator.placeHolderSuffix = ' }}';

Translator.add = function (id: string, ...rest: any[]) {
  dictionary[id] = true;

  return originalAdd.call(Translator, id, ...rest);
};

const checkTranslation = (id: string) => {
  if (!debug) {
    return;
  }

  const defaultDomains = Translator.defaultDomains;
  const domains: (string | undefined)[] = [
    undefined,
    ...(Array.isArray(defaultDomains) ? defaultDomains : [defaultDomains]),
  ];
  const isTranslated = domains.some(domain => dictionary.hasOwnProperty(domain ? `${domain}:${id}` : id));

  if (!isTranslated) {
    console.error('Untranslated: %s', id);
  }
};

Translator.get = function (id: string, ...rest: any[]) {
  checkTranslation(id);

  return originalGet.call(Translator, id, ...rest);
};

Translator.fromJSON = function (data: string | {debug?: boolean}) {
  const parsed = typeof data === 'string' ? JSON.parse(data) : data;
  debug = parsed.debug || false;

  return originalFromJSON.call(Translator, parsed);
};

const translate: Translate = (id, placeholders, count) => Translator.get(id, placeholders, count);

export {translate};
