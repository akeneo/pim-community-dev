import React from 'react';
import {TimeToEnrichFilters} from '../models';
// import {Button, PanelOpenIcon} from 'akeneo-design-system';

type TimeToEnrichHistoricalFiltersProps = {
  filters: TimeToEnrichFilters;
};

const TimeToEnrichHistoricalFilterAbstract = ({filters}: TimeToEnrichHistoricalFiltersProps) => {
  // TODO: implement filters

  return (
    <div>
      <span className={''}>Time-to-enrich</span>, on <span className={''}>Family</span>, during{' '}
      <span className={''}>Last month</span>, compared to <span className={''}>Revenue</span>.
      {/*<Button ghost={true} size={'small'} level={'secondary'}>*/}
      {/*  Open control panel <PanelOpenIcon />*/}
      {/*</Button>*/}
    </div>
  );
};

export {TimeToEnrichHistoricalFilterAbstract};
