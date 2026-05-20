.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer:

JSON payload
============

The scheduler task builds an associative array, encodes it as JSON, and sends a POST request with ``Content-Type: application/json`` via TYPO3’s HTTP client. Field names and shapes below match the current implementation for TYPO3 **13.x**.

Example structure (illustrative values):

.. code-block:: javascript

	{
		"id": "optional-user-or-id-from-extension-configuration",
		"password": "optional-password-from-extension-configuration",
		"version": "13.4.0",
		"modules": [
			{ "name": "my_sitepackage", "version": "1.2.0" }
		],
		"properties": [
			{ "name": "php", "value": "8.2.29" },
			{ "name": "OS", "value": "Linux …" },
			{ "name": "Server API", "value": "fpm-fcgi" },
			{ "name": "Server", "value": "nginx/1.24.0" },
			{ "name": "SQL Version", "value": "8.0.35" }
		]
	}

``version``
   TYPO3 core version string from ``VersionNumberUtility::getNumericTypo3Version()``.

``modules``
   One entry per **local** extension that is installed. Non-local (system/secondary) paths are omitted.

``properties``
   Added when at least one property could be collected. Entries may include fallback strings if a PHP function is disabled or data is unavailable.

HTTP behaviour
==============

* The request method is **POST** with a JSON body.
* A successful run expects HTTP status **200**; other status codes cause an exception with the returned code in the message.

Integration note
================

Earlier versions of this manual documented **Signal/Slot** hooks to alter the payload or low-level HTTP objects. The **TYPO3 13** codebase shipped with this documentation uses a straight POST via ``RequestFactory`` without those hooks. If you need extendability again, implement it in a fork or open a feature request with your use case.
