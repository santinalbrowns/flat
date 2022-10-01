<?php

namespace controllers;

use core\Controller;
use core\http\Request;
use core\http\Response;
use models\User;

class UserController extends Controller
{
    public function profile(Request $request, Response $response)
    {

        $user = new User();

        $user = $user->insert([
         'firstname' => $request->body->firstname,
         'lastname' => $request->body->lastname,
         ]);

        //$me = $user->update(['id' => 2], ['firstname' => 'Santinal']);

        //print_r($me);
        print_r($user);
    }
}