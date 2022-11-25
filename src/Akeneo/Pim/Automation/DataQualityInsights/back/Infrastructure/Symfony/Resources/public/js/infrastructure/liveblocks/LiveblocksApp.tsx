import React from 'react';
import {useOthers, useSelf} from "./liveblocks.config";
import {Avatar} from './components/Avatar';
import styled from 'styled-components';

const LiveblocksApp = () => {
    const others = useOthers();
    const currentUser = useSelf();

    // @ts-ignore
  return (
      <>
          <div>There are {others.count} other user(s) with you on this page</div>
          <Container>
            {currentUser && currentUser.info && (
              // @ts-ignore
              <Avatar picture={currentUser.info.picture} name={currentUser.info.name} />
            )}

            {others.map(({ connectionId, info }) => {
              return (
                // @ts-ignore
                <Avatar key={connectionId} picture={info.picture} name={info.name} />
              );
            })}
          </Container>
      </>
    );
};

const Container = styled.div`
  display: flex;
`;

export {LiveblocksApp};
