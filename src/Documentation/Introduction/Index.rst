.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

This extension send a JSON Post to a given url, with informations about the actual typo3 installation and installed extensions.

With this, it allows you to monitor the informations which TYPO3 Version and which extensions are installed with their appropiate version.

The idea behind this, is to keep track and provide a fast overview for possible necessary security updates in a central place.

A simple to implement scheduler task allows it to transfer the data with the most minimal workload to a given rest url that accepts a json object via POST.

To secure the transfered data, also a user and password can be configured. 
 





