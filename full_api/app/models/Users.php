<?php
namespace Bayt_project;
use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation\Validator\Email as Email;
use Phalcon\Validation\Validator\PresenceOf as RequiredFields;
use Phalcon\Validation\Validator\Uniqueness as Uniqueness;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Regex as RegularExpression;
use Phalcon\Validation\Validator\InclusionIn as InclusionIn;

class Users extends Model
{
	public $id;
	public $name;
	public $email;
	public $password;
	public $role;
	public $session_token;
	public function validation()
    {
        $validator = new Validation();
        $validator->add(
            'name',
            new RequiredFields([
                'model' => $this,
                'message' => 'Error Occured,Name is required',
            ])
        );
		$validator->add(
            'email',
            new Email([
                'model' => $this,
                'message' => 'Error Ocurred,Please enter valid email address'
            ])
        );
		$validator->add(
            'role',
            new InclusionIn([
                'model' => $this,
                'domain' => [
                        'user',
                        'admin',
                    ],
            ])
        );
        $validator->add(
            'email',
            new Uniqueness([
                'model' => $this,
                'message' => 'Error Occured,Email is already exists',
            ])
        );
		 $validator->add(
            'password',
            new StringLength([
				'min' => 8,
				'max' => 12,
				'minMessage' => 'Your password must be at least 8 characters',
				'maxMessage' => 'Your password must be less than 12 characters'
            ])
        );
		$validator->add(
            'name',
            new StringLength([
				'min' => 8,
				'minMessage' => 'Your password must be at least 8 characters'
            ])
        );		
		$validator->add(
		  [
		     "email",
		     "password",
		  ],
			 new RegularExpression(
			  [
				"pattern" => [
					"email" => "/^[a-zA-Z0-9_.+-]+@(?:(?:[a-zA-Z0-9-]+\.)?[a-zA-Z]+\.)?(yahoo|gmail)\.com$/",
					"password"=> "/(?=.*\d)(?=.*[A-Z]).{8,12}/",
			  ],
			   "message" => [
				   "email" => "Error Occured, Valid Domains (Yahoo/Gmail)",
				   "password" => "Wrong Password, at least 1 Capital Letter and 1 Number and 1 Special character",
			  ]
			])
		);
		
		$valid_p=$this->validate($validator);
		if ($valid_p && $GLOBALS["update_password_p"]) {
			$this->password=md5($this->password);
		} 
        return $valid_p;
    }
	
}