<?php

namespace Akeneo\UserManagement\Component;

class UserEvents
{
    const PRE_CREATE_GROUP = 'pim.user.pre_create_group';
    const POST_CREATE_GROUP = 'pim.user.post_create_group';

    const PRE_UPDATE_GROUP = 'pim.user.pre_update_group';
    const POST_UPDATE_GROUP = 'pim.user.post_update_group';

    const PRE_DELETE_GROUP = 'pim.user.pre_delete_group';
    const POST_DELETE_GROUP = 'pim.user.post_delete_group';
}
