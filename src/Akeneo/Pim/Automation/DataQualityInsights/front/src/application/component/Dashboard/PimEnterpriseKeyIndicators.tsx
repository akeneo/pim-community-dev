import React, {FC, useMemo} from 'react';
import {KeyIndicators} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard';
import {useGetSpellcheckSupportedLocales} from '../../../infrastructure';
import {keyIndicatorDescriptorsEE} from './KeyIndicatorDescriptorsEE';
import {keyIndicatorDescriptorsCE} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/keyIndicatorDescriptorsCE';
import {useFeatureFlags} from '@akeneo-pim-community/shared';

type Props = {
  channel: string;
  locale: string;
  family: string | null;
  category: string | null;
};

const PimEnterpriseKeyIndicators: FC<Props> = ({channel, locale, family, category}) => {
  const featureFlags = useFeatureFlags();
  const spellcheckSupportedLocales = featureFlags.isEnabled('data_quality_insights_all_criteria')
    ? useGetSpellcheckSupportedLocales()
    : null;

  const keyIndicatorDescriptors = useMemo(
    () => (spellcheckSupportedLocales?.includes(locale) ? keyIndicatorDescriptorsEE : keyIndicatorDescriptorsCE),
    [locale, spellcheckSupportedLocales]
  );

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
