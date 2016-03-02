<?php

namespace Oro\Bundle\UserBundle;

class OroUserEvents
{
    const PRE_CREATE_GROUP  = 'oro.user.pre_create_group';
    const POST_CREATE_GROUP = 'oro.user.post_create_group';

    const PRE_UPDATE_GROUP  = 'oro.user.pre_update_group';
    const POST_UPDATE_GROUP = 'oro.user.post_update_group';

    const PRE_DELETE_GROUP  = 'oro.user.pre_delete_group';
    const POST_DELETE_GROUP = 'oro.user.post_delete_group';
}
