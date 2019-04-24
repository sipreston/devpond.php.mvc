CREATE TABLE categories
(
	cat_id INT NOT NULL,
	cat_title VARCHAR(200)
);
GO

CREATE TABLE comments
(
	comment_id INT NOT NULL,
	comment_author VARCHAR(255),
	comment_post_id INT NOT NULL,
	comment_email VARCHAR(255),
	comment_content TEXT,
	comment_status VARCHAR(50),
	comment_date DATETIME
);
GO

CREATE TABLE posts
(
	post_id INT NOT NULL,
	post_category_id INT,
	post_title VARCHAR(255),
	post_author INT,
	post_date DATETIME,
	post_image VARCHAR(MAX),
	post_content TEXT,
	post_tags VARCHAR(255),
	post_comment_count INT,
	post_status VARCHAR(255),
	post_views_count INT
);
GO

CREATE TABLE roles
(
	role_id INT NOT NULL,
	role_title VARCHAR(255) NOT NULL
);
GO

CREATE TABLE users
(
	user_id INT NOT NULL,
	username VARCHAR(255) NOT NULL,
	user_password VARCHAR(255) NOT NULL,
	user_firstname VARCHAR(255),
	user_lastname VARCHAR(255),
	user_email VARCHAR(255) NOT NULL,
	user_image VARCHAR(MAX),
	user_role_id INT
);
GO

CREATE TABLE users_online
(
	id INT NOT NULL,
	session VARCHAR(255) NOT NULL,
	time INT NOT NULL
);
GO