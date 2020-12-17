# UPGRADE FROM 4.x TO 5.0

The container parameters `mailer_transport`, `mailer_host`, `mailer_port`, `mailer_encryption`, `mailer_user`, `mailer_password`, `mailer_from_address` have been removed. Instead, the env var `MAILER_URL` should be set like `smtp://localhost:25?encryption=tls&auth_mode=login&username=foo&password=bar&sender_address=no-reply@example.com`.

## Cronjobs

### Added

```bash
*/10 * * * * bin/console pim:data-quality-insights:prepare-evaluations
*/30 * * * * bin/console pim:data-quality-insights:evaluations
15 0 * * * bin/console pim:data-quality-insights:schedule-periodic-tasks
```

## Migration

### Initialize the evaluation of all the products and product models
The following command will populate the evaluation table for products and products model.
Depending on your catalog size this command could take time, a nice progress bar will help you to follow the progression.

`bin/console pim:data-quality-insights:initialize-products-evaluations`
