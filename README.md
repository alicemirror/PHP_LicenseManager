# PHP_LicenseManager
Private licensing system, server side, developed in PHP based on MySQL server

## Running environment
The Licesne Manager actually rung on a Linux Debian (Ubuntu) server AWS EC2 on the Amazon server farm in Virginia

## Note
The licensins system runs on PHP _ MySQL database (the dtabase structure, tables etc. are all defined in separate DB classes)
Is is developed 100% in PHP and server client requests via POST and GET .
Client is expected to be a mobile device. A licensing library is available on Android for certify different authorization
levels, including a demo unregistered version that can be enblaed with the license code in any moment.
Licenses can be migrated between devices.

This system is running on about 500 devices on different customers wihtout problems. THe licensins system can be applied
traversally to any kind of Android applicatio starting from Android 4.x version.
