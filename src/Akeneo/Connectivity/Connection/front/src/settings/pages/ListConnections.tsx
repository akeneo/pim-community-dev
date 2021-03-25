import React, {useEffect} from 'react';
import {useHistory} from 'react-router';
import {ApplyButton, Helper, HelperLink, HelperTitle, PageContent, PageHeader} from '../../common';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {connectionsFetched} from '../actions/connections-actions';
import {ConnectionGrid} from '../components/ConnectionGrid';
import {NoConnection} from '../components/NoConnection';
import {useConnectionsDispatch, useConnectionsState} from '../connections-context';
import {wrongCredentialsCombinationsFetched} from '../actions/wrong-credentials-combinations-actions';
import {WrongCredentialsCombinations} from '../../model/wrong-credentials-combinations';
import {
    useWrongCredentialsCombinationsDispatch,
    useWrongCredentialsCombinationsState,
} from '../wrong-credentials-combinations-context';
import {Breadcrumb} from 'akeneo-design-system';
import {UserButtons} from '../../shared/user';

const MAXIMUM_NUMBER_OF_ALLOWED_CONNECTIONS = 50;

type ResultConnections = Array<Connection>;

export const ListConnections = () => {
    const history = useHistory();

    const connections = useConnectionsState();
    const dispatchConnection = useConnectionsDispatch();

    const wrongCredentialsCombinations = useWrongCredentialsCombinationsState();
    const dispatchCombinations = useWrongCredentialsCombinationsDispatch();

    const listConnectionRoute = useRoute('akeneo_connectivity_connection_rest_list');
    useEffect(() => {
        let cancelled = false;
        fetchResult<ResultConnections, never>(listConnectionRoute).then(
            result => isOk(result) && !cancelled && dispatchConnection(connectionsFetched(result.value))
        );
        return () => {
            cancelled = true;
        };
    }, [listConnectionRoute, dispatchConnection]);

    const listWrongCombinationRoute = useRoute(
        'akeneo_connectivity_connection_rest_wrong_credentials_combination_list'
    );
    useEffect(() => {
        fetchResult<WrongCredentialsCombinations, never>(listWrongCombinationRoute).then(result => {
            if (isOk(result)) {
                dispatchCombinations(wrongCredentialsCombinationsFetched(result.value));
            }
        });
    }, [listWrongCombinationRoute, dispatchCombinations]);

    const handleCreate = () => history.push('/connections/create');

    const systemHref = `#${useRoute('oro_config_configuration_system')}`;

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={systemHref}>
                <Translate id='pim_menu.tab.system' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='pim_menu.item.connection_settings' />
            </Breadcrumb.Step>
        </Breadcrumb>
    );

    const createButton = (
        <ApplyButton
            onClick={handleCreate}
            disabled={Object.keys(connections).length >= MAXIMUM_NUMBER_OF_ALLOWED_CONNECTIONS}
            classNames={['AknButtonList-item']}
        >
            <Translate id='pim_common.create' />
        </ApplyButton>
    );

    const sourceConnections = Object.values(connections).filter(
        connection => FlowType.DATA_SOURCE === connection.flowType
    );
    const destinationConnections = Object.values(connections).filter(
        connection => FlowType.DATA_DESTINATION === connection.flowType
    );
    const otherConnections = Object.values(connections).filter(connection => FlowType.OTHER === connection.flowType);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} buttons={[createButton]} userButtons={<UserButtons />}>
                <Translate id='pim_menu.item.connection_settings' />
            </PageHeader>

            <PageContent>
                <Helper>
                    <HelperTitle>
                        <Translate id='akeneo_connectivity.connection.helper.title' />
                    </HelperTitle>
                    <p>
                        <Translate id='akeneo_connectivity.connection.helper.description' />
                    </p>
                    <HelperLink href='https://help.akeneo.com/pim/articles/what-is-a-connection.html' target='_blank'>
                        <Translate id='akeneo_connectivity.connection.helper.link' />
                    </HelperLink>
                </Helper>

                {Object.keys(connections).length === 0 ? (
                    <NoConnection onCreate={handleCreate} />
                ) : (
                    <>
                        {sourceConnections && sourceConnections.length > 0 && (
                            <div data-testid='data_source'>
                                <ConnectionGrid
                                    connections={sourceConnections}
                                    wrongCredentialsCombinations={wrongCredentialsCombinations}
                                    title={
                                        <Translate
                                            id='akeneo_connectivity.connection.flow_type.data_source'
                                            count={sourceConnections.length}
                                        />
                                    }
                                />
                            </div>
                        )}
                        {destinationConnections && destinationConnections.length > 0 && (
                            <div data-testid='data_destination'>
                                <ConnectionGrid
                                    connections={destinationConnections}
                                    wrongCredentialsCombinations={wrongCredentialsCombinations}
                                    title={
                                        <Translate
                                            id='akeneo_connectivity.connection.flow_type.data_destination'
                                            count={destinationConnections.length}
                                        />
                                    }
                                />
                            </div>
                        )}
                        {otherConnections && otherConnections.length > 0 && (
                            <div data-testid='data_other'>
                                <ConnectionGrid
                                    connections={otherConnections}
                                    wrongCredentialsCombinations={wrongCredentialsCombinations}
                                    title={
                                        <Translate
                                            id='akeneo_connectivity.connection.flow_type.other'
                                            count={otherConnections.length}
                                        />
                                    }
                                />
                            </div>
                        )}
                    </>
                )}
            </PageContent>
        </>
    );
};
