# form4 Status Monitor

TYPO3 scheduler extension that sends **JSON POST** requests to a URL you configure. Each run reports the **TYPO3 core version**, **locally installed extensions**, and optional **environment metadata** (PHP, OS, database server, and similar)—useful for centralized inventories, support workflows, and spotting outdated installations.

**Extension key:** `form4_statusmonitor`

## Requirements

- TYPO3 **14.3** LTS (see `composer.json` / `ext_emconf.php` for the exact constraint)
- **PHP** `^8.2`

## Installation

```bash
composer require form4/form4-statusmonitor
```

Activate the extension in the **Admin Tools → Extensions** module (or via CLI), then configure it and add the scheduler task (see the manual under `Documentation/`).

## Configuration

1. Set **target URL**, and optionally **user** and **password**, in the extension configuration.
2. Create and schedule the **Status Monitor** scheduler task.

Details: `Documentation/UsersManual/Index.rst`.

## Documentation

The Sphinx manual lives in `Documentation/` (Introduction, user manual, developer reference for the JSON payload).

---

## Publishing Package to GitLab Package Registry (Composer)

To release a new version of the package, follow these steps:

1. Create a tag with the new version name, for example, `1.0.1`.
2. Push the newly created tag to the repository.
3. The GitLab job `publish-composer-package` will automatically publish the new version to our package registry.

You can access our package registry [here](https://git.form4.de/groups/form4/unit.dev/backend-php/typo3/typo3.extensions/-/packages).