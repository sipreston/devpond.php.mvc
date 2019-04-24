/*
	If wanting to run this with LocalDb, and login/user must be created first, on the database.
	This is due to LocalDb usually only looking for Windows Authentication
*/

CREATE DATABASE cms
GO;

CREATE LOGIN CMS WITH PASSWORD = 'CMS'
GO

Use cms;
GO

IF NOT EXISTS (SELECT * FROM sys.database_principals WHERE name = N'CMS')
BEGIN
    CREATE USER [CMS] FOR LOGIN [CMS]
    EXEC sp_addrolemember N'db_owner', N'CMS'
END;
GO