import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import React, {FC, useCallback} from 'react';
import {
  KeyIndicatorExtraData,
  Counts,
  KeyIndicatorAttributes,
  KeyIndicatorTips,
  areCountsZero,
  computePercent,
  Tip,
} from '../../../../domain';
import {useGetKeyIndicatorTips} from '../../../../infrastructure/hooks/Dashboard/UseKeyIndicatorTips';
import {useDashboardContext} from '../../../context/DashboardContext';
import {computeTipMessage} from '../../../helper/Dashboard/KeyIndicator';
import {AttributesKeyIndicatorLinkCallback} from '../../../user-actions';
import {KeyIndicatorBase} from './KeyIndicatorBase';
import {KeyIndicatorNoData} from './KeyIndicatorNoData';
import {AttributeMessageBuilder} from './AttributeMessageBuilder';
import {Text, TextWithLink} from './styled';

type Props = {
  type: KeyIndicatorAttributes;
  counts: Counts;
  title: string;
  followResults: AttributesKeyIndicatorLinkCallback;
  extraData?: KeyIndicatorExtraData;
};

export const KeyIndicatorAboutAttributes: FC<Props> = ({children, type, counts, title, followResults, extraData}) => {
  const translate = useTranslate();
  const tips: KeyIndicatorTips = useGetKeyIndicatorTips(type);
  const userContext = useUserContext();
  const {category, familyCode} = useDashboardContext();

  const handleClickOnCount = useCallback(
    (event: React.SyntheticEvent<HTMLElement>) => {
      event.stopPropagation();

      followResults(userContext.get('catalogLocale'), familyCode, category?.id || null, extraData || undefined);
    },
    [userContext, familyCode, category, extraData]
  );

  if (areCountsZero(counts)) {
    return (
      <KeyIndicatorNoData type={type} title={title}>
        {children}
      </KeyIndicatorNoData>
    );
  }

  const percentOK = computePercent(counts.totalGood, counts.totalToImprove);

  const tip: Tip = computeTipMessage(tips, percentOK);

  return (
    <KeyIndicatorBase percentOK={percentOK} titleI18nKey={title} icon={children}>
      <Text>
        <AttributeMessageBuilder counts={counts} onClick={handleClickOnCount} />
        <TextWithLink
          dangerouslySetInnerHTML={{
            __html: translate(tip.message, {link: tip.link || ''}),
          }}
        />
      </Text>
    </KeyIndicatorBase>
  );
};
