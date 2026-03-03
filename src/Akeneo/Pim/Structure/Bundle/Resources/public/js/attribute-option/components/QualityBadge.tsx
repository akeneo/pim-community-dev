import React, {FC} from 'react';
import {Badge} from 'akeneo-design-system';
import {getQualityBadgeLevel} from '../helper/getQualityBadgeLevel';

const translate = require('oro/translator');

type QualityBadgeProps = {
  label: string;
};

const QualityBadge: FC<QualityBadgeProps> = ({label}) => {
  return (
    <Badge level={getQualityBadgeLevel(label)}>
      {translate(`akeneo_data_quality_insights.attribute_grid.quality.${label}`)}
    </Badge>
  );
};

export default QualityBadge;
