<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUser as RequestCreateUser;
use App\User;
use App\Uuid;

class Users extends Controller
{
    public function create(RequestCreateUser $request, Uuid $uuid)
    {
        $validated = $request->validated();

        $user = new User;
        $user->name = $validated['name'];
        $user->token = $uuid->generate();
        $user->save();

        return response()->json([
            'token' => $user->token
        ]);
    }
}
