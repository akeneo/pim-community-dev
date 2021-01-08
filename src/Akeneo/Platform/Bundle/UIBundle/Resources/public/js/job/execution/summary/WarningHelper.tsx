import React, {useState} from 'react';
import styled from 'styled-components';
import {Helper, Link} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Warning} from '../models';
import {InnerTable} from './InnerTable';

const SpacedTable = styled(InnerTable)`
  margin-top: 10px;
`;

type WarningHelperProps = {
  warning: Warning;
};

const WarningHelper = ({warning}: WarningHelperProps) => {
  const translate = useTranslate();
  const [isExpanded, setExpanded] = useState<boolean>(false);
  const toggleExpanded = () => setExpanded(!isExpanded);

  return (
    <Helper level="warning">
      <ul>
        {warning.reason.split('\n').map((reason, index) => (
          <li key={index}>{reason}</li>
        ))}
      </ul>
      <Link onClick={toggleExpanded}>
        {translate(isExpanded ? 'job_execution.summary.hide_item' : 'job_execution.summary.display_item')}
      </Link>
      {isExpanded && <SpacedTable content={warning.item}></SpacedTable>}
    </Helper>
  );
};

export {WarningHelper};
