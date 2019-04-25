# devpond.php.mvc

## Intro
This is an experimental framework and should be considered alpha code. This repo has been created afresh with all previous WIP commits removed. This repo is for code demonstration only.
It is written in PHP 5.6, although there was a view to rewriting it to be PHP7 compliant.
Of course, as any developer does, as the work progresses, I've looked back on some things and thought about what could have been done differently.

## Why not use already available packages?

I originally started writing this framework as a way to find out how things in PHP work. Therefore I wanted to write all classes and libraries myself, as much as possible. So I wrote my own autoloaders (rather than use composer) and my own Database library, as opposed to something like PDO.
Most of the core functionality is in the libs folder.

## Cool stuff

This framework allows for a code first approach. Models classes can be added to the models folder. Each model must also be added to the setModel function (Startup.php). When the UpdateDbModelNew function is run (libs/Vision/VisionModel/Model.php), this will pick up on the defined models and automatically create or update the database tables and columns.

An ORM is also built in which keeps track of already initialised data. 

Installers for MSSQL and MySQL/MariaDb. These automatically install the initial database, with admin user, role and sessions tables.

## Adding new routes
A Controller name defines the initial part of the route (i.e SetupController maps to example.url/setup) with a method with the 'Action' prefix can map to sub-route (so SetupController/InstalldbAction maps to example.url/setup/installdb). 
Actions can also use the prefix post_ to separate post and get requests if required.

## Future

There is still a long way to go before this is considered ready for any practical purposes. There is hand written SQL littered about the code, when ideally it should only be in the providers libraries (libs/Vision/VisionDatabase/Providers) for each DBMS solution. The libraries would ideally convert code calls in to queries.
That said, some functionality does work. It is possible to perform the package install using either MsSQL or MySQL.

The router (libs\Vision\VisionFramework\Bootstrap.php) allows for variable method calling i.e. $controller->$methodName, although it will only look for methods with the 'Action suffix' so this is sort of okay, but not ideal. But it does allow new actions to added and work instantly, without messing about with route maps. Swings and roundabouts.

There's also references to functions that have since been removed but code not cleaned up. Mainly in some of the controllers.

References to super globals would be cleared up and dedicated handlers used to access those.

The code for models is was originally written to use public properties, which is a major no-no. This will be changed so that properties are protected and a handler added to access those properties. Due to its nature some of those properties may have to be none-standard, which isn't ideal.

Lazy loading.

PHP7 upgrade.

Throw more exceptions.

Move the public folders in to a level below, making the code more secure.
