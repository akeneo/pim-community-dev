import React from 'react';
import {PimView, useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb, Table, Button} from 'akeneo-design-system';
import {useSystemInfo} from './SystemInfoHook';
import styled from 'styled-components';

const TableCellList = styled(Table.Cell)`
  > div {
    display: block;
  }
`;

const ButtonCollection = styled.div.attrs(() => ({className: 'AknTitleContainer-actionsContainer AknButtonList'}))`
  > :not(:first-child) {
    margin-left: 10px;
  }
`;

const SystemInfo = () => {
  const translate = useTranslate();
  const systemHomeRoute = useRoute('oro_config_configuration_system');
  const downloadTxtRoute = useRoute('pim_analytics_system_info_download');
  const systemInfoData = useSystemInfo();

  const renderSystemInfoValue: any = (infoValue: any, keyPrefix: string = '') => {
    if (typeof infoValue === 'boolean') {
      return infoValue ? '1' : '0';
    } else if (Array.isArray(infoValue)) {
      return infoValue.map((subInfoValue, subInfoKey) => {
        return (
          <span key={`${subInfoKey}`}>
            {renderSystemInfoValue(subInfoValue)}
            <br />
          </span>
        );
      });
    } else if (typeof infoValue === 'object') {
      return Object.entries(infoValue).map(([subInfoKey, subInfoValue]) => {
        const infoKey = keyPrefix !== '' ? keyPrefix + '.' + subInfoKey : subInfoKey;
        return typeof subInfoValue === 'object' ? (
          renderSystemInfoValue(subInfoValue, subInfoKey)
        ) : (
          <span key={`${infoKey}`}>
            {translate('pim_analytics.info_type.' + infoKey)}: {subInfoValue}
            <br />
          </span>
        );
      });
    } else return infoValue;
  };

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${systemHomeRoute}`}>{translate('pim_menu.tab.system')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_analytics.system_info.title')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
          <ButtonCollection>
            <Button href={downloadTxtRoute} level="tertiary">
              {translate('pim_analytics.system_info.download')}
            </Button>
          </ButtonCollection>
        </PageHeader.UserActions>
        <PageHeader.Title>{translate('pim_analytics.system_info.title')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_analytics.info_header.property')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_analytics.info_header.information')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {Object.entries(systemInfoData).map(([systemInfoType, systemInfoValue]) => {
              return (
                <Table.Row key={systemInfoType}>
                  <Table.Cell>{translate('pim_analytics.info_type.' + systemInfoType)}</Table.Cell>
                  <TableCellList>{renderSystemInfoValue(systemInfoValue)}</TableCellList>
                </Table.Row>
              );
            })}
          </Table.Body>
        </Table>
      </PageContent>
    </>
  );
};

export {SystemInfo};
