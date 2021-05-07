<?php

namespace Akeneo\UserManagement\Component;

class UserEvents
{
    public const PRE_CREATE_GROUP = 'pim.user.pre_create_group';
    public const POST_CREATE_GROUP = 'pim.user.post_create_group';

    public const PRE_UPDATE_GROUP = 'pim.user.pre_update_group';
    public const POST_UPDATE_GROUP = 'pim.user.post_update_group';

    public const PRE_DELETE_GROUP = 'pim.user.pre_delete_group';
    public const POST_DELETE_GROUP = 'pim.user.post_delete_group';
}
