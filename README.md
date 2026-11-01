# Riwaq ERP

The bilingual (English / Arabic) operating system for an Oman real-estate group: marketing, valuations, sales, and rentals in one controlled workspace.

## Product direction

Riwaq is designed around a single source of truth:

`Lead → Evaluation → Listing → Website publication → Viewing → Sale / Lease → Collection → Handover`

The application is multi-company by design. Each company retains its own users, financial records, document numbering, branding, mailbox identities, and access rules. Authorized management can see consolidated reporting.

## First delivery

The current foundation includes a polished responsive operations dashboard with the agreed three-company structure, the operational pipeline, quick actions, priorities, OMR-aware collection indicators, and an Arabic-ready interface direction.

Planned core modules:

- CRM, leads, activities, viewings, and follow-ups
- Property inventory and bilingual publishing controls
- Evaluation requests, inspections, and reports
- Sales, reservations, rentals, renewals, deposits, and handover
- Manual cash, cheque, transfer, and terminal-payment recording
- Oman VAT-configurable documents and audit trail
- Outlook-style multi-mailbox communication centre
- API integration for the future bilingual listings website

## Technical baseline

- Laravel 13 / PHP 8.3+
- MariaDB-compatible data layer for the Plesk deployment
- Server-rendered UI so the production application does not depend on a modern Node runtime for daily operation
- Vite assets are optional for development builds; confirm the Plesk PHP and Node settings before production deployment

## Local validation in this cloud workspace

This workspace uses a Docker PHP environment because PHP is not installed on the host image:

```sh
docker run --rm -v "$PWD:/app" -w /app composer:2 php artisan test
```

## Deployment target

The intended production host is Plesk at `realestate.techittechnologies.com`. Deployment will be performed only after the application workflow is implemented, tested, and Plesk access is provided.

See [the Plesk deployment runbook](docs/PLESK_DEPLOYMENT.md) for host requirements, secure environment configuration, deployment commands, and post-deployment checks.
