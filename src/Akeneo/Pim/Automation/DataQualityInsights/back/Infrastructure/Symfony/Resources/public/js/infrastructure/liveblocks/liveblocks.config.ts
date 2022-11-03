import {createClient, Json} from '@liveblocks/client';
import { createRoomContext } from '@liveblocks/react';
const client = createClient({
    publicApiKey: 'pk_dev_G4kaySINOe1hRVzJvUMR0oh6YjmCOV5Hb_u3ZuBfQNlo6bL-jZBwcemOmSPLqC-K',
});
type UserMeta = {
    id?: string;
    info?: Json;
};

export const {
    suspense: {
        RoomProvider,
        useOthers,
    },
} = createRoomContext<UserMeta>(client);
