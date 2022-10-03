import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, CloseIcon, getColor, IconButton} from 'akeneo-design-system';
import {ProductFile} from '../model/ProductFile';
import {useIntl} from 'react-intl';
import {Comment as CommentReadModel} from '../model/Comment';
import {Metadata} from './Metadata';
import {Discussion} from './Discussion';

const Panel = styled.div<AkeneoThemedProps & {currentProductFile: ProductFile | null}>`
    width: ${({currentProductFile}) => (currentProductFile ? '30%' : '0')};
    transition-property: width;
    transition-duration: 0.5s;
    box-shadow: 0 0 16px rgba(89, 146, 199, 0.1);
    display: flex;
    flex-direction: column;
    height: 100vh;
`;

type Props = {
    productFile: ProductFile | null;
    closePanel: () => void;
};

const StyledIconButton = styled(IconButton)`
    color: ${getColor('grey100')};
    position: absolute;
    top: 27px;
    right: 30px;

    &:hover:not([disabled]) {
        background-color: transparent;
        color: ${getColor('grey100')};
    }
`;

const ProductFilePanel = ({productFile, closePanel}: Props) => {
    const intl = useIntl();
    if (null === productFile) {
        return <></>;
    }
    let comments = productFile.retailerComments
        .concat(productFile.supplierComments)
        .sort(
            (a: CommentReadModel, b: CommentReadModel) =>
                new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime()
        );

    return (
        <>
            {productFile ? (
                <Panel currentProductFile={productFile}>
                    <StyledIconButton
                        data-testid={'close-panel-icon'}
                        icon={<CloseIcon size={16} />}
                        title={intl.formatMessage({
                            defaultMessage: 'Close',
                            id: 'rbrahO',
                        })}
                        ghost={'borderless'}
                        onClick={closePanel}
                    />
                    <Metadata productFile={productFile} />
                    <Discussion comments={comments} productFileIdentifier={productFile.identifier} />
                </Panel>
            ) : (
                <Panel currentProductFile={productFile} />
            )}
        </>
    );
};

export {ProductFilePanel};
