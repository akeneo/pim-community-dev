import {useEffect, useState} from 'react';
import {useIsMounted, useRoute, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {DataMapping, Column, isAttributeDataMapping, isPropertyDataMapping} from '../models';

const useSearchDataMappings = (dataMappings: DataMapping[], columns: Column[], search: string) => {
  const [matchingDataMappings, setMatchingDataMapping] = useState<DataMapping[]>(dataMappings);
  const searchAttributesRoute = useRoute('pimee_tailored_import_search_attributes_action');
  const isMounted = useIsMounted();
  const translate = useTranslate();
  const localeCode = useUserContext().get('catalogLocale');

  useEffect(() => {
    if ('' === search) {
      setMatchingDataMapping(dataMappings);

      return;
    }

    const attributeTargetCodes = dataMappings
      .filter(dataMapping => isAttributeDataMapping(dataMapping))
      .map(({target}) => target.code);

    const matchingPropertyCodes = dataMappings
      .filter(
        dataMapping =>
          isPropertyDataMapping(dataMapping) &&
          translate(`pim_common.${dataMapping.target.code}`).toLowerCase().includes(search.toLowerCase())
      )
      .map(({target}) => target.code);

    const matchingColumnUuids = columns
      .filter(({label}) => label.toLowerCase().includes(search.toLowerCase()))
      .map(({uuid}) => uuid);

    const searchAttributes = async () => {
      const response = await fetch(searchAttributesRoute, {
        body: JSON.stringify({
          search,
          attribute_codes: attributeTargetCodes,
          locale_code: localeCode,
        }),
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
      });

      const matchingAttributeCodes = await response.json();

      if (!isMounted()) return;

      setMatchingDataMapping(
        dataMappings.filter(
          ({sources, target}) =>
            matchingAttributeCodes.includes(target.code) ||
            matchingPropertyCodes.includes(target.code) ||
            sources.some(columnUuid => matchingColumnUuids.includes(columnUuid))
        )
      );
    };

    void searchAttributes();
  }, [isMounted, translate, dataMappings, columns, searchAttributesRoute, search, localeCode]);

  return matchingDataMappings;
};

export {useSearchDataMappings};
