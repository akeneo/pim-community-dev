import {createClient} from '@liveblocks/client';
import {createRoomContext} from '@liveblocks/react';
const Routing = require('routing');

const client = createClient({
    authEndpoint: async (room: string) => {
        const response = await fetch(Routing.generate('liveblocks_auth', {roomId: room}), {
            method: "POST",
            headers: {
                "Authentication": "token",
                "Content-Type": "application/json",
            }
        });

        return await JSON.parse(await response.json());
    },
});

type UserMeta = {
    id: string;
    info: {
        name: string;
        picture: string;
    };
};

export const {
    suspense: {
        RoomProvider,
        useOthers,
        useSelf,
    },
} = createRoomContext<UserMeta>(client);
