import React, {FC} from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TimePeriodFilter} from '../Filters';

type Props = {
  timePeriod: string;
};

const Header: FC<Props> = ({timePeriod}) => {
  const translate = useTranslate();

  return (
    <SectionTitle>
      <SectionTitle.Title>
        {translate('akeneo_data_quality_insights.dqi_dashboard.score_distribution.title')}
      </SectionTitle.Title>
      <SectionTitle.Spacer />
      <TimePeriodFilter timePeriod={timePeriod} />
    </SectionTitle>
  );
};

export {Header};
