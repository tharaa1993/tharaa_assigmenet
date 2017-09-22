- Note : please check "api_call.txt" to access this api
- Go to index.php (bootstrap file and change postgres config)


This APi is contains the following MAIN features:
1. User Registration
	- User fields
		name:		Required , min : 8 chars
		email:		Unique, Valid, Valid domain (Yahoo, Gmail)
		password:	between 8-12 character, at least one special, one capital letter, pne number
		role:		admin/user
		*sesstion token : 	generated randomly and stored in DB
		*password:		Will encrypted by the system and stored in DB
2. USer Login
	- The user should enter his/her email and password
	- if email & password are valid, the system will return session token else 401, Unauthorized Access

3. Create a Car Posts	
	- Car Fields:
		title:		required [20,100] chars
		description:	required , min:100 chars
		car_maker:	(FK in Car_Maker(id))
		picture:	optional
		Extra:		the user add any field in json format ex. Color,price, is_manual {"color":"red","price":1000}	

4. Create a Car Maker
	- Csr Maker Fields:
		ID
		NAME
		COUNTRY

- Also, It's support the following services:
	- CREATE NEW POST
	- GET PUBLISHED POST
	- GET PUBLISHED POSTS USING USER ID
	- GET PUBLISHED POSTS USING CAR MAKER ID (json/xml format)
	- SEARCH FOR SPECIFC USER USING USER NAME
	- SEARCH FOR SPECIFC USER USING USER ID
	- UPDATE USER INFO USING USER_ID 
	- DELETE USER USING USER ID
	- ADD NEW CAR MAKER
	- RETRIVE EXISTING CAR MAKER
	- GET CAR POSTS USING CAR MAKER ID
	- SEARCH FOR A CAR USING ANY FIELD IN EXTRA FIELD (EX. COLOR, PRICE, IS_MANUAL,...)  
	- PUBLISH POST (ACCESSABLE BY ADMIN ONLY)

Data Model:
------------------------Users Table --------------------------------
CREATE TABLE public.users
(
    name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    email character varying(500) COLLATE pg_catalog."default" NOT NULL,
    password character varying(50) COLLATE pg_catalog."default" NOT NULL,
    role character varying(200) COLLATE pg_catalog."default",
    session_token character varying(500) COLLATE pg_catalog."default",
    id integer NOT NULL DEFAULT nextval('users_id_seq'::regclass),
    CONSTRAINT users_pkey PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
)
----------------------------------------------------------------------

------------------------Car Maker Table --------------------------------
CREATE TABLE public.car_maker
(
    maker_name text COLLATE pg_catalog."default" NOT NULL,
    country text COLLATE pg_catalog."default",
    id integer NOT NULL DEFAULT nextval('car_maker_id_seq'::regclass)
)
WITH (
    OIDS = FALSE
)
---------------------------------------------------------------------
-------------------------Posts Table -------------------------------
CREATE TABLE public.posts
(
    post_id integer NOT NULL DEFAULT nextval('post_id'::regclass),
    title text COLLATE pg_catalog."default",
    description character varying(1000) COLLATE pg_catalog."default",
    picture character varying(100) COLLATE pg_catalog."default",
    extra jsonb,
    user_id integer,
    is_published boolean DEFAULT false,
    car_maker integer,
    CONSTRAINT posts_pkey PRIMARY KEY (post_id)
)
-----------------------------------------------------------------------
-----------------------User_Roles------------------------------------
CREATE TABLE public.user_roles
(
    role_name text COLLATE pg_catalog."default" NOT NULL,
    functions character varying(1000) COLLATE pg_catalog."default",
    CONSTRAINT user_roles_pkey PRIMARY KEY (role_name)
)

		