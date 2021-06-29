import React, {FC} from 'react';
import styled from 'styled-components';

const translate = require('oro/translator');

const Helper = styled.div`
  margin-top: -20px;
  background-color: #f5f9fc;
  padding: 13px 13px 13px 0;
  display: flex;
`;

const HelperIcon = styled.div`
  padding: 0 13px 0 13px;
  margin-right: 13px;
  border-right: 1px #c7cbd4 solid;
  background-image: url(/bundles/pimui/images/icon-info.svg);
  background-repeat: no-repeat;
  background-size: 16px 16px;
  background-position: center;
  width: 39px;
`;

const HelperLink = styled.a`
  color: #9452ba;
  text-decoration: underline #9452ba;
`;

const HelperMessage: FC = () => {
  return (
    <Helper>
      <HelperIcon />
      <div>
        {translate('akeneo_data_quality_insights.attribute_group.helper_dqi_info')}&nbsp;
        <HelperLink href="https://help.akeneo.com/pim/serenity/articles/understand-data-quality.html" target="_blank">
          {translate('akeneo_data_quality_insights.attribute_group.helper_dqi_link')}
        </HelperLink>
      </div>
    </Helper>
  );
};

export {HelperMessage};
