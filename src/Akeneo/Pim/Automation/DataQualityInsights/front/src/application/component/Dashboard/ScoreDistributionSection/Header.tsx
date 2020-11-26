import React, {FC} from 'react';
import {TimePeriodFilter} from '../Filters';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Props = {
  timePeriod: string;
};

const Header: FC<Props> = ({timePeriod}) => {
  const translate = useTranslate();
  return (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{translate('akeneo_data_quality_insights.dqi_dashboard.score_distribution.title')}</span>
      <div className="AknFilterBox AknFilterBox--search">
        <div className="AknFilterBox-list filter-box">
          <TimePeriodFilter timePeriod={timePeriod} />
        </div>
      </div>
    </div>
  );
};

export {Header};
