# UPGRADE FROM 4.0 TO 5.0

The container parameters `mailer_transport`, `mailer_host`, `mailer_port`, `mailer_encryption`, `mailer_user`, `mailer_password`, `mailer_from_address` have been removed. Instead, the env var `MAILER_URL` should be set like `smtp://localhost:25?encryption=tls&auth_mode=login&username=foo&password=bar&sender_address=no-reply@example.com`.
