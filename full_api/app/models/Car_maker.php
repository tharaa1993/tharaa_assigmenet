<?php
namespace Bayt_project;
use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation\Validator\PresenceOf as RequiredFields;

class Car_maker extends Model
{
	public $id;
	public $maker_name;
	public $country;
	public function validation()
    {
        $validator = new Validation();
         $validator->add(
		  [
			  "maker_name",
			  "country",
		  ],
		  new RequiredFields(
			  [
					"message" => [
					 "maker_name"  => "The Maker Name is required",
					 "country" => "The Maker Country is required",
				 ],
			 ]
		 )
		);
		
		$valid_p=$this->validate($validator);
        return $valid_p;
    }
	
}