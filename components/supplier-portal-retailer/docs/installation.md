# Installation

In order to install the Supplier Portal locally, follow these steps:

* Go to the root directory of the `pim-enterprise-dev` repository on the `master` branch. Be sure you're up-to-date.
* Run `make dependencies -B && make pim-dev`

The installation is done. The Supplier Portal project is split into 2 distinct applications, see below.

## Retailer app
If you want to access the retailer app, run the following command from the root directory:

```
yarn --cwd=components/supplier-portal-retailer/front app:start
```

The micro application should have started on http://localhost:3001/.

## Supplier app
If you want to access the supplier app, run the following command from the root directory:

```
yarn --cwd=components/supplier-portal-supplier/front app:start
```

The micro application should have started on http://localhost:3000/.


## Troubleshooting

- The retailer app is failing with the message "Unhandled Rejection (Error): You are not logged in the PIM"

You have to log into the PIM first on http://localhost:8080/.
