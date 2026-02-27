<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user/permissions', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles'       => $user->getRoleNames(),
        ]);
    });
});