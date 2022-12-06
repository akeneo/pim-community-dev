import React, {FC, PropsWithChildren} from 'react';
import {EnabledInput} from './EnabledInput';
import {findFirstError} from '../utils/findFirstError';
import {InfoRoundIcon} from 'akeneo-design-system';
import {translate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {CatalogFormValues} from '../models/CatalogFormValues';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import ReactDOM from 'react-dom';

type Props = {
    values: CatalogFormValues;
    errors: CatalogFormErrors;
    headerContextContainer: HTMLDivElement | undefined;
};

const Container = styled.div`
    margin-top: 22px;
    display: flex;
`;

const Helper = styled.div`
    line-height: 15px;
    width: 330px;
    margin-top: 24px;
    margin-left: 7px;
    display: flex;
`;

const InfoRoundIconStyled = styled(InfoRoundIcon)`
    width: 30px;
    height: 30px;
`;
const Description = styled.div`
    margin-left: 6px;
`;

const CatalogStatusWidget: FC<PropsWithChildren<Props>> = ({values, errors, headerContextContainer}) => {
    if (headerContextContainer === undefined) {
        return null;
    }

    return ReactDOM.createPortal(
        <>
            <Container>
                <EnabledInput value={values.enabled} error={findFirstError(errors, '[enabled]')} />
                <Helper>
                    <InfoRoundIconStyled color='#5992c7' size={16} />
                    <Description>{translate('akeneo_catalogs.catalog_status_widget.helper')}</Description>
                </Helper>
            </Container>
        </>,
        headerContextContainer
    );
};

export {CatalogStatusWidget};
