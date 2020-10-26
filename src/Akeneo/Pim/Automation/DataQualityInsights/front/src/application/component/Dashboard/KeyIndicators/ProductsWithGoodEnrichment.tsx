import React, {FC} from 'react';
import {KeyIndicator} from "./KeyIndicator";
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import {EditIcon} from "akeneo-design-system";
import {Tips} from "../../../../domain";

type Props = {
  type: string;
  ratio?: number;
  total?: number;
};

const ProductsWithGoodEnrichment: FC<Props> = ({ratio, total}) => {
  const translate = useTranslate();

  return (
    <KeyIndicator
      tips={tips}
      ratio={ratio}
      total={total}
      title={translate(`akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title`)}
      entitiesToWorkOnMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
    >
      <EditIcon/>
    </KeyIndicator>
  );
}

const tips: Tips = {
  'first_step': [
    {
      message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.first_step.message1',
      link: 'https://help.akeneo.com/pim/serenity/articles/manage-data-quality.html',
    },
    {
      message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.first_step.message2',
      link: 'https://help.akeneo.com/pim/serenity/articles/sequential-edit.html',
    },
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.first_step.message3'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.first_step.message4'},
  ],
  'second_step': [
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.second_step.message1'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.second_step.message2'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.second_step.message3'},
    {
      message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.second_step.message4',
      link: 'https://help.akeneo.com/pim/serenity/articles/manage-data-quality.html',
    },
  ],
  'third_step': [
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.third_step.message1'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.third_step.message2'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.third_step.message3'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.third_step.message4'},
  ],
  'perfect_score_step': [
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.perfect_score_step.message1'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.perfect_score_step.message2'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.perfect_score_step.message3'},
    {message: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.messages.perfect_score_step.message4'},
  ],
};

export {ProductsWithGoodEnrichment};
