import React, {FC, useMemo} from 'react';
import {KeyIndicators} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard';
import {useGetSpellcheckSupportedLocales} from '../../../infrastructure';
import {keyIndicatorDescriptorsEE} from './KeyIndicatorDescriptorsEE';
import {keyIndicatorDescriptorsCE} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/keyIndicatorDescriptorsCE';

type Props = {
  channel: string;
  locale: string;
  family: string | null;
  category: string | null;
};

const PimEnterpriseKeyIndicators: FC<Props> = ({channel, locale, family, category}) => {
  const spellcheckSupportedLocales = useGetSpellcheckSupportedLocales();

  const keyIndicatorDescriptors = useMemo(
    () => (spellcheckSupportedLocales?.includes(locale) ? keyIndicatorDescriptorsEE : keyIndicatorDescriptorsCE),
    [locale, spellcheckSupportedLocales]
  );

  if (spellcheckSupportedLocales === null) {
    return <></>;
  }

  return (
    <KeyIndicators
      channel={channel}
      locale={locale}
      family={family}
      category={category}
      keyIndicatorDescriptors={keyIndicatorDescriptors}
    />
  );
};

export {PimEnterpriseKeyIndicators};
