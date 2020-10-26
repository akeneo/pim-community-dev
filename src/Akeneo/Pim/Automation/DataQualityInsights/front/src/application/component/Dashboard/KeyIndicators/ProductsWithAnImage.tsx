import React, {FC} from 'react';
import {KeyIndicator} from "./KeyIndicator";
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import {AssetCollectionIcon} from "akeneo-design-system";
import {Tips} from "../../../../domain";

type Props = {
  type: string;
  ratio?: number;
  total?: number;
};

const ProductsWithAnImage: FC<Props> = ({ratio, total}) => {
  const translate = useTranslate();

  return (
    <KeyIndicator
      tips={tips}
      ratio={ratio}
      total={total}
      title={translate(`akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title`)}
      entitiesToWorkOnMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
    >
      <AssetCollectionIcon/>
    </KeyIndicator>
  );
}

const tips: Tips = {
  'first_step': [
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.first_step.message1'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.first_step.message2'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.first_step.message3'},
  ],
  'second_step': [
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.second_step.message1'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.second_step.message2'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.second_step.message3'},
  ],
  'third_step': [
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.third_step.message1'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.third_step.message2'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.third_step.message3'},
  ],
  'perfect_score_step': [
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.perfect_score_step.message1'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.perfect_score_step.message2'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.messages.perfect_score_step.message3'},
  ],
};

export {ProductsWithAnImage};
