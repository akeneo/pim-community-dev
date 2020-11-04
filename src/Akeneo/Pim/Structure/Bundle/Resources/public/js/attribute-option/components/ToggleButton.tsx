import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeContext} from '../contexts';

const ToggleButton = () => {
  const translate = useTranslate();
  const attributeContext = useAttributeContext();

  return (
    <div className="switch switch-small has-switch" data-on-label="Yes" data-off-label="No">
      <div className={`switch-animate switch-${attributeContext.autoSortOptions ? 'on' : 'off'}`}>
        <input
          id="auto-sort-options"
          type="checkbox"
          name="auto_option_sorting"
          checked={attributeContext.autoSortOptions}
          readOnly={true}
        />
        <span className="switch-left switch-small">{translate('Yes')}</span>
        <label
          className="switch-small"
          onClick={() => attributeContext.toggleAutoSortOptions()}
          role="toggle-sort-attribute-option"
          htmlFor="auto-sort-options"
        >
          &nbsp;
        </label>
        <span className="switch-right switch-small">{translate('No')}</span>
      </div>
    </div>
  );
};

export default ToggleButton;
