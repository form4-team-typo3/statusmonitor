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
      //this properties part was added with version 0.0.3 
      //the following data is intended as an example for all given properties by default 
      "properties": [
         {
            // if the phpversion() is enabled
            "name": "php",
            "value": "7.2"
        },
        {
            "name": "OS",
            "value": "Output depending on support of php_uname()"
        },
        {
            "name": "SERVER API",
            "value": "Output depending on support of php_sapi_name()"
        },
        {
            "name": "SERVER",
            "value": "Output depending on $_SERVER['SERVER_SOFTWARE']"
        },
        {
            "name": "SQL Version",
            "value": "Returns the version of the DB Connection via TYPO3 API"
        }
    ]
	}


Extending the JSON data
=======================

If you want to extend the send data array before it is converted to JSON with json_encode(), you can declare a signal like this in your ext_localconf.php:

.. code-block:: php

	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Dispatcher::class);
	$signalSlotDispatcher->connect(
		\FORM4\Statusmonitor\Utility\StatusmonitorUtility::class,
		'ModifyDataArrayBeforeJsonEncode',
		\YourVendor\YourExtension\YourPath\YourClass::class,
		'NameOfYOurMethod' 
	);

In your target method you will get one parameter, which is the array with the body data before it is converted to JSON with json_encode().
Return this array after you have modified it.  

Changing the httpRequest Configuration (TYPO3 7.6)
==================================================

As the extension uses "HttpRequest" class of the core for TYPO3 7.6, so you have the possibilty to override the given httpRequest object.

For that you can use the signal/slot 

.. code-block:: php

   $signalSlotDispatcher->connect(
      \FORM4\Statusmonitor\Utility\StatusmonitorUtility::class,
      'ModifyHttpRequest',
      \YourVendor\YourExtension\YourPath\YourClass::class,
      'NameOfYOurMethod' 
   );
   //see \TYPO3\CMS\Core\Http\HttpRequest->setConfiguration() for allowed configuration keys/values
   

You will get the HttpRequest object as argument. Return it after you have changed it.

Changing the Request object (TYPO3 8.7)
=======================================

The usage of the HttpRequest Class has been replaced with beginning from TYPO3 8.7, so that here we use the TYPO3/\CMS/\Core/\Http/\Request class for sending the request.
But here you also have the possibility to override the given request and the options. But keep in mind that the request is immutable.
The signal/slot offers you here 2 arguments: TYPO3/\CMS/\Core/\Http/\Request and the options.  

Use the following signal/slot:

.. code-block:: php

   $signalSlotDispatcher->connect(
      \FORM4\Statusmonitor\Utility\StatusmonitorUtility::class,
      'ModifyRequestAndOptions',
      \YourVendor\YourExtension\YourPath\YourClass::class,
      'NameOfYOurMethod' 
   );
   
   // as the TYPO3\CMS\Core\Http\Request is immutable it returns a object with your changes and you should return the new object 
   // example:
   
    $request = $request->withMethod('GET')->withAddedHeader($name, $value)->withProtocolVersion($version);
    return $request;
    
