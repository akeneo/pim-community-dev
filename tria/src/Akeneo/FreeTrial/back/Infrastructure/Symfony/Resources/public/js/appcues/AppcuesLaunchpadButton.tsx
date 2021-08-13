import React from 'react';
import {IconButton} from "akeneo-design-system";
import {LaunchpadIcon} from "../../icons/LaunchpadIcon";
import styled from "styled-components";
import AppcuesOnboarding = require("../onboarding/appcues-onboarding");

const StyledIconButton = styled(IconButton)`
  margin-right: 10px;
`;

const AppcuesLaunchpadButton = () => {
    return (
      <StyledIconButton
        ghost
        level="tertiary"
        icon={<LaunchpadIcon />}
        onClick={() => AppcuesOnboarding.loadLaunchpad('#appcues-launchpad-btn')}
        title="Launchpad"
      />
    );
};

export {AppcuesLaunchpadButton}
