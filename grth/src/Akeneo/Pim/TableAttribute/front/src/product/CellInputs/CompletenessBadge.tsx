import React, {useMemo} from 'react';

import {Badge} from 'akeneo-design-system';
import {RecordCompleteness} from '../../models';

type CompletenessBadgeProps = {
  completeness?: RecordCompleteness;
};

const CompletenessBadge: React.FC<CompletenessBadgeProps> = ({completeness}) => {
  const ratio = useMemo(() => {
    if (!completeness) return;

    const {required, complete} = completeness;
    if (0 === completeness.required) {
      return 0;
    }
    return Math.round((100 * complete) / required);
  }, [completeness]);

  const level = useMemo(() => {
    if (completeness?.complete !== undefined && !(completeness.complete > 0)) {
      return 'danger';
    } else if (completeness?.complete === completeness?.required) {
      return 'primary';
    } else {
      return 'warning';
    }
  }, [completeness]);

  if (!completeness) return null;

  return <Badge level={level}>{ratio}%</Badge>;
};

export {CompletenessBadge};
