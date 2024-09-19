# Mazuma Acquisition Flow Wordpress Plugin
This Wordpress plugin acts as a wrapper to mount the Vite bundled ReactJS Acquisition Flow application into a Wordpress page.

## Installation
These steps can be performed on a local development environment and uploaded as a bundle to Wordpress, or directly on the live Wordpress server.

1. Clone this repository
2. Run `composer install` (or `php composer.phar install` if Composer is not globally available) in the API directory to install the required dependencies
3. Copy `api/.env.example` to `api/.env` and fill in the required environment variables
4. Activate the plugin in the Wordpress admin and configure the settings

## Configuration
Various configuration options are available in the plugin settings page, which can be found when activated under the headings Settings > Acquisition Flow

These settings all have hardcoded default values in the built application, so they are not required to be set, but they will be used prerfenetially if defined.
 

### General Settings
**Path / Page Name**
The path to the page where the Acquisition Flow will be mounted. This should be a valid Wordpress page slug, and ideally the page should be blank with no content.

The javascript and css assets will be loaded on this page.

**Book a call URL**
The URL to Calendly, where the user will be directed if out of office hours.

### Quote Calculation Monthly Base Rates
The base rates for the quote calculation, in GBP excl VAT. These are used to calculate the monthly cost of the plan.

**Base Rates**
These set the base rate for each plan based on the Company Type selected.

### Payroll
**Payroll Fee Matrix**
This is a JSON array of the payroll fee matrix. This is used to calculate the payroll fee based on the number of employees.

The format of each child array is the following:
```
[
  number, // the upper bound of the number of employees
  number  // the fee for this number of employees, in GBP excl. VAT
]
```

### Additional Monthly Fees

**VAT registered**
The additional monthly fee for VAT registered companies, in GBP excl. VAT.

## New Client Fees

**Setup fee**
A one-off setup fee for new clients, in GBP excl. VAT.