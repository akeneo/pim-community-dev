import React, {FC} from 'react';
import {useAttributeGroupState} from '../../../infrastructure/hooks/AttributeGroup/useAttributeGroupState';

const translate = require('oro/translator');
const securityContext = require('pim/security-context');

type Props = {
  groupCode: string;
};

const ToggleActivation: FC<Props> = ({groupCode}) => {
  const {isGroupActivated, toggleGroupActivation} = useAttributeGroupState(groupCode);

  const isGranted = securityContext.isGranted('akeneo_data_quality_insights_activation_attribute_group_edit');
  const switchClassName = `switch switch-small has-switch ${isGranted ? '' : 'deactivate'}`;

  return (
    <div className={switchClassName} data-on-label="Yes" data-off-label="No">
      <div className={`switch-animate switch-${isGroupActivated ? 'on' : 'off'}`}>
        <input id="enable-dqi" type="checkbox" checked={isGroupActivated} readOnly={true} />
        <span className="switch-left switch-small" style={{fontSize: '13px'}}>
          {translate('Yes')}
        </span>
        <label className="switch-small" onClick={() => isGranted && toggleGroupActivation()} htmlFor="enable-dqi">
          &nbsp;
        </label>
        <span className="switch-right switch-small" style={{fontSize: '13px'}}>
          {translate('No')}
        </span>
      </div>
    </div>
  );
};

export {ToggleActivation};
