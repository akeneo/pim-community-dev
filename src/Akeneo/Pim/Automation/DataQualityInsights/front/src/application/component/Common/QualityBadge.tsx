import React, {FC} from 'react';

const translate = require('oro/translator');

type QualityBadgeProps = {
  label: string;
};

const QualityBadge: FC<QualityBadgeProps> = ({label}) => {
  const classList: string[] = [
    'AknDataQualityInsightsQualityBadge',
    ` AknDataQualityInsightsQualityBadge--${label.replace('_', '-')}`
  ];

  return (
    <span className={classList.join(' ')}>
      {translate(`akeneo_data_quality_insights.attribute_grid.quality.${label}`)}
    </span>
  );
}

export default QualityBadge;
