<?php

namespace Apiex\Actions\Auth;

/**
 * @package zafex/apiexlara
 *
 * @author Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 *
 * @link https://github.com/zafex
 */

use Apiex\Helpers\Privileges;
use Apiex\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

trait Authentication
{
    /**
     * @param Request $request
     */
    public function authenticate(Request $request, Privileges $privileges, Settings $settings)
    {
        $credentials = $request->only('name', 'password');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return app('ResponseError')->withValidation($validator, 'authenticate')->send();
        }

        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                return app('ResponseError')->withMessage(__('invalid_credentials'))->send(400);
            }
        } catch (JWTException $e) {
            return app('ResponseError')->withMessage(__('could_not_create_token'))->send(500);
        }

        // re-cache all privileges
        $privileges->load();

        // re-cache all settings information
        $settings->load();

        return app('ResponseSingular')->setItem($token)->send(200);
    }
}
