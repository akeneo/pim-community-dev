import React from 'react';
import {useHistory} from 'react-router';
import {Figure, FigureCaption, FigureImage} from '../../common';
import imgUrl from '../../common/assets/illustrations/api.svg';

interface Props {
    code: string;
    label: string;
}

export const App = ({code, label}: Props) => {
    const history = useHistory();

    return (
        <Figure onClick={() => history.push(`/apps/${code}/edit`)}>
            <FigureImage src={imgUrl} alt={label} />
            <FigureCaption title={label}>{label}</FigureCaption>
        </Figure>
    );
};
