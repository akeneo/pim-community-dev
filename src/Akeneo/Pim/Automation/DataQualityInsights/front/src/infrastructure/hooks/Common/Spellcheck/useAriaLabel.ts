import {useEffect, useState} from 'react';
import {MistakeElement} from '../../../../application/helper';

const translate = require('oro/translator');

const DEFAULT_ARIA_LABEL = translate('akeneo_data_quality_insights.spellcheck.popover.default_aria_label');

const useAriaLabel = (mistake: MistakeElement | null): string => {
  const [ariaLabel, setAriaLabel] = useState<string>(DEFAULT_ARIA_LABEL);

  useEffect(() => {
    if (mistake && mistake.text) {
      setAriaLabel(
        translate('akeneo_data_quality_insights.spellcheck.popover.default_aria_label', {
          mistake: mistake.text,
        })
      );
    } else {
      setAriaLabel(DEFAULT_ARIA_LABEL);
    }
  }, [mistake]);

  return ariaLabel;
};

export default useAriaLabel;
