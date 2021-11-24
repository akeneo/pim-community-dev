# Development workflow with a micro-frontend

Now that you have created your micro-frontend, you can start working in the Create React App (CRA).

It's important to make sure that your PIM frontend is correctly built because the CRA is using the same routes & translations files as the PIM.
Then you need to have a running PIM and be logged in because it's likely that your micro-frontend will have to reach your backend.
Finally, to start the CRA, just run the following command:

```bash
  cd /path/to/your/micro-frontend
  yarn app:start
```

This will launch a local server on port 3000 that will autorefresh when detecting changes in your micro-frontend.
If you created your micro-frontend using the previous cookbook, you should now see a `Hello world` on http://localhost:3000.
To have a better representation of what your feature will look like in the PIM, the micro-frontend is also using a fake PIM wrapper that you can customize to your needs.

## Troubleshooting

### What if I want to add a new translation?

When adding new translations, you need to dump the translations using the Symfony command and re-run the CRA to recopy the translation file.

```bash
  cd /path/to/pim
  bin/console oro:translation:dump en_US
  cd /path/to/your/micro-frontend
  yarn app:start
```

### What if I want to add a new route?

When adding new routes, you need to clear your cache, rebuild your PIM front and then re-run the CRA.

```bash
  cd /path/to/pim
  rm -rf var/cache
  bin/console pim:installer:assets --symlink --clean
  cd /path/to/your/micro-frontend
  yarn app:start
```

### What if I want to see my feature in the PIM

If you added the build step described in the `how_to_create_a_micro_frontend` cookbook, you shoud only need to rebuild the front packages and rebuild the PIM front:

```bash
  cd /path/to/pim
  yarn packages:build
  yarn webpack-dev
```

Alternatively you can also run `make front` to rebuild everything.
