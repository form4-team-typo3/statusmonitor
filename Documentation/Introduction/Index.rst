.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

This extension adds a **scheduler task** that sends a **JSON payload** (HTTP POST) to an endpoint you configure. The payload includes the **TYPO3 core version**, all **locally installed** extensions with their versions, and—since v0.0.3—additional **properties** such as PHP version, OS, server API, web server signature, and database server version where available.

Typical uses:

* Central **inventory** of TYPO3 installations and loaded extensions
* **Support** and operations workflows that need quick environment facts
* Spotting installations that may need **updates** or security patches

The endpoint must accept JSON via POST. You can optionally set a **username** and **password** in the extension configuration; they are included in the JSON body if configured.

Data collection is designed to be lightweight: only the scheduler task runs on your schedule.
