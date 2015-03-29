Download tems.zip and uncompress all sources in a folder say, 'tems', under Apache's (or other http server's, based on your environment) default web root folder or as specified by your web-hosting company.

NOTE: <HOSTNAME> is a place holder and has to be replaced by your actual host name.

* * * * * STOP STOP STOP * * * * * 

* * * * * Read this section very carefully if you will install on a hosted server. * * * * *

Do the following and have all information handy to proceed with installation.
  a) Make a note of the MySQL database server name or IP Address,
  b) Create a database [using your cpanel, or as you prefer, just an EMPTY DATABASE [HAVE NOTHING IN IT.],
  c) Set one user as DBA to this database and make a note of this user id and its password,
    [Many users prefer to use the master user id that you use to login to the database in a hosted environment. It is your personal choise; we recommend to create a user id that has virtually all authorities on this database only and has no access to other databases in the same server. This is just a best practise on database security.]
    [Some hosting companies force to have a special character in the name or password. This is certainly a good idea, however, we found that sometimes those special characters have conflicts with the programming languages. You cannot use any of ?{}|&~![()^" characters.]
    [We will use this user id to create tables and insert initial data but the programs do not store this information anywhere and forget as soon as the necessary works are done.]
  d) Set one database user having read/write permission on the database and make a note of it and its password.
    [This user id and it's credentials will be through out application. This is not mandatory but highly recommended for security reasons. Some of our users do not want to maintain many users and use the same user id for all purposes. This is purely your preference.]
  e) Give write permissions to 'core/config', 'log', and 'images' directories.
    Notes: 
    1) After installation is all complete, change the 'core/config' directory's permission back to read-only -- This is very improtant for security, this directory contains sensitive information beyond this application.
    2) After you change the logo of your organization by editing 'Company' record, change 'images' directory's permission to read only.
    3) If you install on a localhost (say, in a laptop), you may not have to do any of these as mentioned above. In the most common scenarios, our program is smart enough to take care of these for you .

Once you have all these, you can proceed further.

Type http://<HOSTNAME>/tems/index.php in the address bar of your browser. This will redirect you to the appropriate page of installation. Provide information as asked and click "Install" button. 

At the end of this installation process, it will create a file named 'Database.ini' in 'core/config' directory, so the user who will install this application should have a write authority in tems/core/config directory. Also it will write log/installation.log file with details of how it progressed. You do not have to worry about these. These are just troubleshooting tools. But you must have write permission to log directory.

Installation process will create 
  a) database including all tables, views etc,
  b) install messages, literals, and some template records **
  c) an sceleton company record which you will replace with your own company information, and
  d) admin user record with id = admin, and password = admin, so that you can login to the application and setup the rest.

** You can change or delete the template records like tasks, expense categories, authorizations, if that does not meet your business requirements. They are given as a jump start for a first time user.

Security notes:
a) TEMS does not use the DBA user id for anything other than installation and it does not remember or store it anywhere.

b) TEMS stores the normal user id and password in plain text in core/config/Database.ini file. It will be your responsibility to protect this file so that unwanted user cannot see this file.

Recommendations:
a) After you Successfully login
	1. Change admin password
	2. Change the 'config' folder's permission to read-only (chmod 744 config).
b) After you change the company logo, change the 'images' folder's permission to read-only (chmod 744 images).
c) Register your application.
d) Monitor the files in the log folder. If you find any file whose name begins with log_, send to us to get a free fix.
========================================================================================

Database.ini will look like as below. [This is just an example, your information will be different based on what you have provided.]

[database]
db_server = mysql_server_name
db_database = tems_database_name
db_user = tems_user
db_password = tems_user_password

Note: How to go to the installation page forcefully?
Normally you will never go to the installation page after you have installed the application. However, for any reason if you are forced to go to the installation page use the following url.

http://<HOSTNAME>/tems/install/index.php

========================================================================================

Fist few steps to make your experience enjoyable.
1) Use Login url is http://<HOSTNAME>/tems/index.php
2) Login as admin (default password is admin),
3) Change the admin password to a complex password, logout and verify that password is working fine,
4) Go to 'Company' menu under System Configuration, edit the company master record,
5) Go to Authorization, set one authority having all authorities, and another for normal user, [Note: admin has all authorities, you cannot limit admin's authorities. It is hardcoded in the application.]
6) Create a few users, [Try to specify as much information as you can at this stage. You can always change these later.]
7) Create Expense Categories, and Tasks,
8) Create accounts and then projects,
9) Assign a) users to a project, and b) tasks to a users.

Please pay attention to sequence of set up, especially steps 7, 8, and 9, until you become an expert.

You are all set to use time and expense.

Good luck.

========================================================================================
Email us your questions, comments and experiences at support@temsonline.com.
========================================================================================