import React, {FC, PropsWithChildren} from 'react';
import {Badge} from 'akeneo-design-system';

type Props = {
    label: string;
};

const Dummy: FC<PropsWithChildren<Props>> = ({label}) => {
    return <Badge level='primary'>{label}</Badge>;
};

export {Dummy};
