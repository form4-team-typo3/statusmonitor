.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

This Extension send a Json Object Post to a given url, with Informations about the actual typo3 installation and components.
This is quite useful if you are monitoring a great number of TYPO3 installations, and want to collect the data at a central place
with the help of a rest api that can accept the data at a given url.

For that this extension provides a scheduler task that sends the data to a given rest url that accepts a json object via POST.

The extension contains a signal/slot to extend the given data.





