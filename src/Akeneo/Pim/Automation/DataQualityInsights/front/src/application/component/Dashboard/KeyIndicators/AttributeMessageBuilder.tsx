import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Counts} from '../../../../domain';
import {roughCount} from '../../../helper/Dashboard/KeyIndicator';
import {messageBuilder} from './messageBuilder';

interface Props {
  counts: Counts;
  onClick: (event: React.SyntheticEvent<HTMLElement>) => void;
}

export const AttributeMessageBuilder: FC<Props> = ({counts: {totalToImprove}, onClick}) => {
  const translate = useTranslate();

  const roughTotalToImprove: number = roughCount(totalToImprove);

  const roughTotalToImproveText = translate(
    'akeneo_data_quality_insights.dqi_dashboard.key_indicators.attributes',
    {count: roughTotalToImprove.toString()},
    roughTotalToImprove
  );

  if (roughTotalToImprove === 0) {
    return null;
  }

  return (
    <>
      {messageBuilder({
        '<improvable_attributes_count_link/>': <button onClick={onClick}>{roughTotalToImproveText}</button>,
      })(translate('akeneo_data_quality_insights.dqi_dashboard.key_indicators.attributes_to_work_on'))}{' '}
    </>
  );
};
