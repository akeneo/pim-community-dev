import React, {FC} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, Link, CheckRoundIcon} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {ScopeList} from '../ScopeList';
import ScopeMessage from '../../../model/Apps/scope-message';

const AppTitle = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 40px;
    margin: 0;
`;

const Helper = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    line-height: 18px;
    margin: 10px 0 20px 0;
    width: 280px;
`;

const NoScope = styled.div`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('bigger')};
    font-weight: normal;
    line-height: 21px;
    margin-bottom: 13px;
    display: flex;
    align-items: center;

    & > svg {
        margin-right: 10px;
        color: ${getColor('grey', 100)};
    }
`;

const ScopeListTitle = styled.h3`
    color: ${getColor('grey', 140)};
    font-size: 17px;
    font-weight: 600;
    margin: 30px 0 10px 0;
`;

interface Props {
    appName: string;
    scopeMessages: ScopeMessage[];
    oldScopeMessages?: ScopeMessage[] | null;
}

export const ScopeListContainer: FC<Props> = ({appName, scopeMessages, oldScopeMessages}) => {
    const translate = useTranslate();

    const title =
        scopeMessages.length === 0
            ? translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope_title', {
                  app_name: appName,
              })
            : translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.title', {app_name: appName});

    return (
        <>
            <AppTitle>{title}</AppTitle>
            <Helper>
                <p>{translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper')}</p>
                <Link
                    href={
                        'https://help.akeneo.com/pim/serenity/articles/how-to-connect-my-pim-with-apps.html#all-editions-authorization-step'
                    }
                >
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.helper_link')}
                </Link>
            </Helper>
            {0 === scopeMessages.length ? (
                <NoScope>
                    <CheckRoundIcon size={24} />
                    {translate('akeneo_connectivity.connection.connect.apps.wizard.authorize.no_scope')}
                </NoScope>
            ) : (
                <>
                    <ScopeList scopeMessages={scopeMessages} highlightMode={oldScopeMessages ? 'new' : null} />
                    {oldScopeMessages && oldScopeMessages.length > 0 && (
                        <>
                            <ScopeListTitle>
                                {translate(
                                    'akeneo_connectivity.connection.connect.apps.wizard.authorize.is_allowed_to'
                                )}
                            </ScopeListTitle>
                            <ScopeList scopeMessages={oldScopeMessages} highlightMode={'old'} />
                        </>
                    )}
                </>
            )}
        </>
    );
};
