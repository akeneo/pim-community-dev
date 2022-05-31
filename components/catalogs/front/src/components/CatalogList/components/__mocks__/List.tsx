import React, {FC, PropsWithChildren} from 'react';

type Props = {
    owner: string;
};

const List: FC<PropsWithChildren<Props>>  = ({owner}) => (<>list {owner}</>);

export {List};
