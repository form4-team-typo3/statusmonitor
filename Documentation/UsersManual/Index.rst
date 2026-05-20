.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Installation
============

Install the extension with Composer from the root of your TYPO3 project:

.. code-block:: bash

	composer require form4/form4-statusmonitor

Alternatively, install the package from the TYPO3 Extension Repository (TER) and upload it in **Admin Tools → Extensions**.

Activation
----------

Enable the extension in **Admin Tools → Extensions**.

Extension configuration
-----------------------

Open the extension configuration and set:

* **Target URL** — full URL including ``https://`` (must pass PHP’s URL validation).
* **User** and **password** — optional; embedded in the JSON payload when set.

Scheduler task
--------------

Create a **scheduler task** of type **Status Monitor Task**, assign a sensible frequency (for example daily), and save.

The task uses the URL and credentials from the extension configuration. Ensure the receiving server accepts POST requests with a JSON body and returns HTTP **200** on success.
