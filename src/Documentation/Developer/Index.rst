.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer:

JSON
====

this a short overview of the structure of the JSON file that will be send.

.. code-block:: javascript

	{
		//if you have set a usernme/id in the scheduler task
		"id": "username",
		//if you have set a password in the scheduler task
		"password": "password",
		//TYPO3 Version
		"version":"8.7",
		//here follows a list of the typo3 extensions that are local extensions and installed.
		"modules": [
		{
			"name": "realurl",
			"version": "8.15"
		},
		{
			"name": "staticfilecache",
			"version": "2.1"
		},
			//and so on
		]
	}


Extending the JSON data
=======================

If you want to extend the send Data , you can declare a signal like this in your ext_localconf.php

.. code-block:: php

	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Dispatcher::class);
	$signalSlotDispatcher->connect(
		\FORM4\Statusmonitor\Utility\StatusmonitorUtility::class,
		'AddToDataToArrayBeforeJsonEncode',
		\YourVendor\YourExtension\YourPath\YourClass::class,
		'NameOfYOurMethod' 
	);

In your target method you will get one parameter, which is the parent object /\FORM4/\Statusmonitor/\Utility/\StatusmonitorUtility.
There are 3 Methods in the parent object available which might be helpful to manipulate the array before it is decoded as a JSON object.

.. code-block:: php

	$pObj->addToJsonArray('yourKey', $value); // just if you want to add a own key value pair to the existing array.

	// if you want to manipulate the whole aray use the following methods to get the array, modify it like you want and set it again.
	$data = $pObj->getJsonArray();
	//... your changes
	$pObj->setJsonArray($data);



