import {httpGet} from './fetch';
import {Router} from '../dependenciesTools';
import {LocaleCode} from '../models';
import {Select2Option} from '../components/Select2Wrapper';

type AttributeOptionCode = string;

const AttributeOptionDataProvider = (
  currentCatalogLocale: string,
  attributeId: number,
  term: string,
  page?: number
) => {
  const options: any = {
    type: 'code',
    locale: currentCatalogLocale,
  };
  if (page) {
    options.page = page;
    options.limit = 20;
  }
  return {
    class: 'Akeneo\\Pim\\Structure\\Component\\Model\\AttributeOption',
    dataLocale: currentCatalogLocale,
    collectionId: attributeId,
    search: term,
    options: options,
  };
};

const getAttributeOptionsByIdentifiers: (
  identifiers: AttributeOptionCode[],
  currentCatalogLocale: LocaleCode,
  attributeId: number,
  router: Router
) => Promise<Select2Option[]> = async (
  identifiers,
  currentCatalogLocale,
  attributeId,
  router
) => {
  let dataParams = AttributeOptionDataProvider(
    currentCatalogLocale,
    attributeId,
    ''
  );
  dataParams = {
    ...dataParams,
    options: {...dataParams.options, ids: identifiers},
  };
  const url = router.generate('pim_ui_ajaxentity_list', dataParams);
  const response = await httpGet(url);
  const json = await response.json();

  return json.results;
};

export {
  AttributeOptionDataProvider,
  getAttributeOptionsByIdentifiers,
  AttributeOptionCode,
};
