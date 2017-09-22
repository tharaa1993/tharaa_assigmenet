<?php
use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Postgresql as DbAdapter;
use Phalcon\Http\Response;
session_start();
$_SESSION['token']='71a0eedcd07c96ef1f33c9e5dc50ac54'; // session for admin
$_SESSION['token']='cfdf00fc546691c254bd8797b65e4205'; //session for user
$update_password_p=0; // this variable is a global variable used to encrypt updated password in Users.php
// Use Loader() to autoload our model
$loader = new Loader();

$loader->registerNamespaces(
    [
        'Bayt_project' => __DIR__ . '/app/models/',
    ]
);
$loader->register();
$di = new FactoryDefault();
// Set up the database service
$di->set(
    'db',
    function () {
        return new DbAdapter(
            [
                'host'     => 'localhost',
                'username' => 'postgres',
                'password' => 'th9932011801',
                'dbname'   => 'postgres',
            ]
        );
    }
);

// Create and bind the DI to the application
$app = new Micro($di);
/*
	USER LOGIN API, RETURN USER SESSION ID IF EMAIL AND PASSWORD ARE CORRECT otherwise, UNAUTHORIZED ACCESS!
	URL:	http://localhost/full_api/users/login
	Method:	POST
	data:	{"email":"tharaa_18885@gmail","password":"Thara123^"}
*/
$app->post(
    '/users/login',
    function () use($app) {
		$user = $app->request->getJsonRawBody();
		$phql = 'SELECT session_token FROM Bayt_project\Users where email=:email: AND password=:password:';
        $data = $app->modelsManager->executeQuery(
            $phql,
            [
                'email' => $user->email,
                'password' => md5($user->password),
            ]
        );
        $response = new Response();
		$data=json_encode($data);
        if ($data!='[]') {
            $response->setJsonContent($data);
        } else {
            $response->setStatusCode(401, 'Unauthorized Access!');
            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => 'Invalid Email or Password',
                ]
            );
        }
        return $response;
    }
);
/*
	RETRIVE ALL REGISRED USERS!
	URL:	http://localhost/full_api/users
	Method:	GET
*/
$app->get(
    '/users',
    function () use ($app) {
		$phql = 'SELECT * FROM Bayt_project\Users';
        $users = $app->modelsManager->executeQuery($phql);
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id'   => $user->id,
                'name' => $user->name,
            ];
        }
        echo json_encode($data);
    }
);

/*
	SEARCH FOR SEPECIFC USER USING THIER NAME (INSENSETIVE)
	RETRIVE ALL USERS USING USER ID!
	URL:	http://localhost/full_api/users/search/Tharaa
	Method:	GET
*/
$app->get(
    '/users/search/{name}',
    function ($name) use ($app) {
		$phql = 'SELECT * FROM Bayt_project\Users WHERE lower(name) LIKE :name: ORDER BY name';
        $users = $app->modelsManager->executeQuery(
            $phql,
            [
                'name' => '%' . strtolower($name) . '%'
            ]
        );
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id'   => $user->id,
                'name' => $user->name,
            ];
        }
        echo json_encode($data);
    }
);
/*
	RETRIVE ALL USERS USING USER ID!
	URL:	http://localhost/full_api/users/1
	Method:	GET
*/
$app->get(
    '/users/{id:[0-9]+}',
    function ($id) use ($app) {
		$phql = 'SELECT * FROM Bayt_project\Users WHERE id = :id:';
        $user = $app->modelsManager->executeQuery(
            $phql,
            [
                'id' => $id,
            ]
        )->getFirst();
        // Create a response
        $response = new Response();
		$data=[];
        if ($user === false) {
			$status= 'NOT-FOUND';
        } else {
            $status='FOUND';
			$data=[
					'id'   => $user->id,
					'name' => $user->name
				];
        }
		$response->setJsonContent(
			[
				'status' => $status,
				'data'=>$data,
			]
		);
        return $response;
    }
);
/*
	ADD NEW USER (USER REGISTRATION) - THE PASSWORD WILL ENCRYPTED BY THE SYSTEM AND SESSION_TOKEN WILL GENERATED RANDOMLY
	USER ROLE BY DEFAULT = USER
	URL:	http://localhost/full_api/users
	Method:	POST
	data :	{"name":"Tharaa Ashour","email":"tharaa_1993@yahoo.com","password":"Tharaa123","role":"user"}
*/
$app->post(
    '/users',
    function () use($app) {
		$user = $app->request->getJsonRawBody();
		$role=isset($user->role)? $user->role : "user";
		$phql = 'INSERT INTO Bayt_project\Users (name, email, password,role,session_token) VALUES (:name:, :email:, :password:,:role:,:session_token:)';
        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
				'role' => $role,
				'session_token' => md5(uniqid(rand(), true))
            ]
        );
        $response = new Response();   // Create a response
        // Check if the insertion was successful
        if ($status->success() === true) {
            $response->setStatusCode(201, 'Created');
            $user->id = $status->getModel()->id;
            $response->setJsonContent(
                [
                    'status' => 'OK',
                    'data'   => $user,
                ]
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, 'Conflict');
            // Send errors to the client
            $errors = [];
            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }
        return $response;
    }
);
/*	
	UPDATE USER INFO BASED ON PRIMARY KEY!
	Url:	//localhost/full_api/users/1
	Method:	PUT
	data:	{"name":"Tharaa Ashour","role":"user","email":"tharaa_18885@gmail.com"}
*/
$app->put(
    '/users/{id:[0-9]+}',
    function ($id) use ($app) {
		$user = $app->request->getJsonRawBody();
		$update_parameter['id']=$id;
		$update_statement='';
		if (isset($user->name)) {
			$update_parameter['name']=$user->name;
			$update_statement.='name = :name:,';
		}
		if (isset($user->role)) {
			$update_parameter['role']=$user->role;
			$update_statement.='role = :role:,';
		}
		if (isset($user->email)) {
			$update_parameter['email']=$user->email;
			$update_statement.='email = :email: ,';
		}
		if (isset($user->password)) {
			$GLOBALS["update_password_p"]=1;
			$update_parameter['password']=$user->password;
			$update_statement.='password = :password:,';
		}
        $response = new Response();// Create a response
		if ($update_statement!=null) {
			$update_statement=rtrim($update_statement,',');
			$phql = 'UPDATE Bayt_project\Users SET '.$update_statement.' WHERE id = :id:';
			$status = $app->modelsManager->executeQuery(
				$phql,$update_parameter
			);
			 if ($status->success() === true) {  // Check if the insertion was successful
				$response->setJsonContent(
					[
						'status' => 'OK',
						'messages' => 'Updated!'
					]
				);
			} else {
				// Change the HTTP status
				$response->setStatusCode(409, 'Conflict');

				$errors = [];

				foreach ($status->getMessages() as $message) {
					$errors[] = $message->getMessage();
				}

				$response->setJsonContent(
					[
						'status'   => 'ERROR',
						'messages' => $errors,
					]
				);
			}
		} else {
			$response->setJsonContent(
					[
						'status' => 'ERROR',
						'messages'=>'Error, Updated columns ar null, please pass a column to make update action!'
					]
				);
		}
        return $response;
    }
);

/*
	Delete User using user_id (PK)
	URL:	http://localhost/full_api/users/1
	Method:	DELETE
*/
$app->delete(
    '/users/{id:[0-9]+}',
    function ($id) use ($app) {
		$phql = 'DELETE FROM Bayt_project\Users WHERE id = :id:';
        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                'id' => $id,
            ]
        );
        // Create a response
        $response = new Response();
        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    'status' => 'OK',
					'messages'=>'Deleted'
                ]
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, 'Conflict');
            $errors = [];
            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }
        return $response;
    }
);
// Adds New Car Maker
/*		
	URL:	http://localhost/full_api/car_maker
	Method:	POST
	Data:	{"maker_name":"Toyota","country":"Japan"}
*/
$app->post(
    '/car_maker',
    function () use($app) {
		$car_maker = $app->request->getJsonRawBody();
		$phql = 'INSERT INTO Bayt_project\Car_maker (maker_name, country) VALUES (:maker_name:, :country:)';
        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                'maker_name' => $car_maker->maker_name,
                'country' => $car_maker->country,
            ]
        );
        // Create a response
        $response = new Response();
        if ($status->success() === true) {
            $response->setStatusCode(201, 'Created');
            $car_maker->id = $status->getModel()->id;
            $response->setJsonContent(
                [
                    'status' => 'OK',
                    'data'   => $car_maker,
                ]
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, 'Conflict');
            $errors = [];

            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }
        return $response;
	}
);

// Retrieves ALL CAR MAKERS - THE USER SHOULD BE LOGGED IN AND HIS ROLE IS "USER"!
// THE "TOKEN" SESSION SHOULD BE PREDFIEND!
/*
	URL:	http://localhost/full_api/posts/car_maker
	METHOD:	GET
*/
$app->get(
    '/posts/car_maker',
    function () use ($app) {
		$is_user=0;
		//CHECK SESSION TOKEN
		if (isset($_SESSION['token'])) {
			$phql = 'SELECT role FROM Bayt_project\Users where session_token=:token: limit 1';
			$users = $app->modelsManager->executeQuery($phql, ['token' => $_SESSION['token']]);
			//CHECK USER ROLE FOR THE CURRENT USER , IF ROLE != USER, 401 ACCESS DENNIED!
			foreach ($users as $user) {
				if ($user->role=='user') { $is_user=1;}
			}
			if ($is_user) {
				// GET ALL EXISTING CAR MAKER!
				$phql = 'SELECT * FROM Bayt_project\Car_maker';
				$car_maker = $app->modelsManager->executeQuery($phql);
				echo json_encode($car_maker);
			}
		}
		if (!$is_user) {
			$response=new Response();
			$response->setStatusCode(401, 'Access denied');
            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => 'This Api accessable only by the logged in users with user role!',
                ]
            );
			return $response;
		}
    }
);
//Retrive all posts by car maker id
/*
	URL:	http://localhost/full_api/posts/car_maker/2
	METHOD:	GET
*/
$app->get(
    '/posts/car_maker/{id:[0-9]+}',
    function ($id) use ($app) {
		$phql = 'SELECT * FROM Bayt_project\Bayt_posts where is_published="true" and car_maker=:car_maker:';
		$posts = $app->modelsManager->executeQuery(
            $phql,
            [
                'car_maker' => $id,
            ]
        );
		if (isset($_SERVER['CONTENT_TYPE']) && preg_match('/xml/',$_SERVER['CONTENT_TYPE'])) {
			$data='<?xml version="1.0" encoding="utf-16" ?> ';
			foreach ($posts as $car_post) {
				$data.="<post><post_id>".$car_post->post_id."</post_id>";
				$data.="<title>".$car_post->title."</title>";
				$data.="<description>".$car_post->description."</description>";
				$data.="<user_id>".$car_post->user_id."</user_id>";
				$data.="<is_published>".$car_post->is_published."</is_published>";
				$data.="<car_maker>".$car_post->car_maker."</car_maker>";
				$data.="<picture>".$car_post->picture."</picture>";
				$data.="<extra>".$car_post->extra."</extra></post>";
			}
			echo $data;
		} else {	
			echo json_encode($posts);
		}
    }
);
//SEARCH FOR A CAR WITH SPECIFC EXTRA FIELD
/*
	URL:	http://localhost/full_api/posts/extra/color,red
	METHOD:	GET
*/
$app->get(
    '/posts/extra/{name}',
    function ($search_filter) use ($app) {
		$search_filter=explode(",",$search_filter);
		$field_name=$search_filter[0];
		$field_value=$search_filter[1];
		$phql = "SELECT * FROM Bayt_project\Bayt_posts where is_published='true' AND jsonb_extract_path_text(extra, :field_name:) =:field_value:";
		$posts = $app->modelsManager->executeQuery(
            $phql,
            [
                'field_name' => $field_name,
				'field_value' => $field_value,
            ]
        );
        echo json_encode($posts);
    }
);
// Retrieves all posts
/*
	URL:	http://localhost/full_api/posts/
	METHOD:	GET
*/
$app->get(
    '/posts',
    function () use ($app) {
		$phql = 'SELECT * FROM Bayt_project\Bayt_posts where is_published="true"';
        $posts = $app->modelsManager->executeQuery($phql);
        $data = [];
        foreach ($posts as $post) {
            $data[] = [
                'post_id'   => $post->post_id,
                'title' => $post->title,
				'description' => $post->description,
				'picture_link'=>$post->picture,
				'user_id'=>$post->user_id,
				'car_maker'=>$post->car_maker,
            ];
        }
        echo json_encode($data);
    }
);
// Retrieves all posts by user_id
/*
URL: 	http://localhost/full_api/posts/11111
Metohd:	GET
*/
$app->get(
    '/posts/{id:[0-9]+}',
    function ($id) use ($app) {
		$phql = 'SELECT * FROM Bayt_project\Bayt_posts where is_published="true" and user_id=:user_id: LIMIT 10';
		$posts = $app->modelsManager->executeQuery(
            $phql,
            [
                'user_id' => $id,
            ]
        );
        echo json_encode($posts);
    }
);

//publish post - accessable by admin
// EX> http://localhost/full_api/posts/publish/10 - Method: PUT - Data : {"is_published":"1"}
$app->put(
    '/posts/publish/{id:[0-9]+}',
    function ($id) use ($app) {
		$response=new Response();
		if (!isset($_SESSION['token'])) {
			$response->setStatusCode(401, 'Access denied');
            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => 'This Api accessable only by the logged in users with admin role!',
                ]
            );
		} else {
			$phql = 'SELECT role FROM Bayt_project\Users where session_token=:token: limit 1';
			$users = $app->modelsManager->executeQuery($phql, ['token' => $_SESSION['token']]);
			$is_admin=0;
			foreach ($users as $user) {
				if ($user->role=='admin') { $is_admin=1;}
			}
			if ($is_admin) {
				$user = $app->request->getJsonRawBody();
				$phql = 'UPDATE Bayt_project\Bayt_posts SET is_published = :is_published: where post_id=:id:';
				$status = $app->modelsManager->executeQuery(
					$phql,
					[
						'id'   => $id,
						'is_published' => $user->is_published,
					]
				);
				// Check if the insertion was successful
				if ($status->success() === true) {
					$response->setStatusCode(201, 'Created');
					$post->id = $status->getModel()->id;
					$response->setJsonContent(['status' => 'OK','data'   => $post,]);
				} else {
					$response->setStatusCode(409, 'Conflict');
					// Send errors to the client
					$errors = [];
					foreach ($status->getMessages() as $message) {
						$errors[] = $message->getMessage();
					}
					$response->setJsonContent(
						[
							'status'   => 'ERROR',
							'messages' => $errors,
						]
					);
				}
			} else {
				$response->setStatusCode(401, 'Access denied');
				$response->setJsonContent(
					[
						'status'   => 'ERROR',
						'messages' => 'This Api accessable only by the logged admin!',
					]
				);
			}
		}
		return $response;

		
	}
);
// Adds a new posts
// EX: URL: http://localhost/full_api/posts
// Data :	{"title":"Pruis Car Full Options !!! ","description":"In February 2011, Toyota asked the public to decide on what the most proper plural form of Prius should be, with choices including Prien, Prii, Prium, Prius, or Priuses. The company said it would use the most popular choice in its advertising and on 20 February announced that Prii was the most popular choice, and the new official plural designation.In Latin prius is the neuter singular of the comparative form (prior, prior, prius) of an adjective with only comparative and superlative (the superlative being primus, prima, primum). Consequently, like all third declension words, the plural in Latin was priora (cf. Latin declension) which was used by the Lada Priora in 2007.","user_id":"11111","car_maker":"1","extra":"{\"color\":\"blue\",\"is_manual\":\"0\",\"price\":\"10000\"}"}
// Method:	POST
$app->post(
    '/posts',
    function () use($app) {
		$response=new Response();
		if (!isset($_SESSION['token'])) {
			$response->setStatusCode(401, 'Access denied');
            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => 'This Api accessable only by the logged in users!',
                ]
            );
		}  else {
			$posts = $app->request->getJsonRawBody();
			$phql = 'INSERT INTO Bayt_project\Bayt_posts (title, description, picture,user_id,car_maker,extra) VALUES (:title:, :description:, :picture:,:user_id:,:car_maker:,:extra:)';
			$picture=isset($posts->picture)?$posts->picture:null;
			$status = $app->modelsManager->executeQuery(
				$phql,
				[
					'title' =>$posts->title,
					'description' => $posts->description,
					'picture' => $picture,
					'user_id' => $posts->user_id,
					'car_maker' => $posts->car_maker,
					'extra'=> $posts->extra,
				]
			);
			$response = new Response();
			// Check if the insertion was successful
			if ($status->success() === true) {
				$response->setStatusCode(201, 'Created');
				$response->setJsonContent(
					[
						'status' => 'OK'
					]
				);
			} else {
				// Change the HTTP status
				$response->setStatusCode(409, 'Conflict');
				// Send errors to the client
				$errors = [];
				foreach ($status->getMessages() as $message) {
					$errors[] = $message->getMessage();
				}
				$response->setJsonContent(
					[
						'status'   => 'ERROR',
						'messages' => $errors,
					]
				);
			}
		}
        return $response;
    }
);


$app->handle();
