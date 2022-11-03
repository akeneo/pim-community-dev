import React from 'react';
import { useOthers } from "./liveblocks.config";

const LiveblocksApp = () => {
    const others = useOthers();

    return (
        <div>There are {others.count} other user(s) with you on this page</div>
    );
};

export {LiveblocksApp};
