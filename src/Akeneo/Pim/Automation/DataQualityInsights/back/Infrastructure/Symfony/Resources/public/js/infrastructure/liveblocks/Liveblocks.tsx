import React, {FC} from 'react';
import {unstable_batchedUpdates} from 'react-dom';
import {ClientSideSuspense} from '@liveblocks/react';
import {RoomProvider} from './liveblocks.config';
import {LiveblocksApp} from './LiveblocksApp';

type Product = {
  identifier: string;
};
type Props = {
  product: Product;
};
const Liveblocks: FC<Props> = ({product}) => {
  return (
    <RoomProvider id={product.identifier} initialPresence={{}} unstable_batchedUpdates={unstable_batchedUpdates}>
      <ClientSideSuspense fallback={<div>Loading...</div>}>{() => <LiveblocksApp />}</ClientSideSuspense>
    </RoomProvider>
  );
};

export {Liveblocks};
