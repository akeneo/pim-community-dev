insert into oro_user_access_role (user_id, role_id)
with
  user_data as (
    select id from oro_user where username='adminakeneo'
  ),
  role_data as (
    select distinct role_id from oro_user_access_role
)
select user_data.id, role_data.role_id
from role_data
cross join user_data
where role_data.role_id not in (
  select role_id
  from oro_user_access_role
    join user_data on oro_user_access_role.user_id = user_data.id
)
