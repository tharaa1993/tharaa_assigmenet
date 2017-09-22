<?php
namespace Bayt_project;
use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation\Validator\PresenceOf as RequiredFields;
use Phalcon\Validation\Validator\StringLength as StringLength;

class Bayt_posts extends Model
{
	public $post_id;
	public $title;
	public $description;
	public $car_maker;
	public $picture;
	public $extra;
	public $user_id;
	public $is_published;
	
	public function validation()
    {
       $validator = new Validation();
       $validator->add(
		  [
			  "title",
			  "description",
		  ],
		  new RequiredFields(
			  [
					"message" => [
					 "title"  => "The Post Title is required",
					 "description" => "The Post Description is required",
				 ],
			 ]
		 )
		);
		 $validator->add(
            'description',
            new StringLength([
				'min' => 100,
				'minMessage' => 'Your post description must be at least 8 characters',
            ])
        );
		$validator->add(
            'title',
            new StringLength([
				'min' => 20,
				'max'=>100,
				'minMessage' => 'Your post title must be at least 8 characters',
				'maxMessage' => 'Your post title must be at least 8 characters'
            ])
        );
		
		$valid_p=$this->validate($validator);
        return $valid_p;
    }
	
}