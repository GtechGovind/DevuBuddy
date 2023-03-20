<?php

namespace App\Http\Controllers\Modules\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Modules\Utility\Constants;
use App\Http\Controllers\Modules\Utility\GLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PDOException;

class UserAuthController extends Controller
{
    public function validateUser(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::table('user')
            ->where('username', '=', $request->input('username'))
            ->where('password', '=', $request->input('password'))
            ->first();

        if (is_null($user)) {
            return Response([
                'status' => false,
                'code' => Constants::USER_NOT_FOUND,
                'message' => "User not found please register !"
            ]);
        }

        return Response([
            'status' => true,
            'code' => Constants::USER_FOUND,
            'user' => $user
        ]);

    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'number' => 'required|unique:user',
            'age' => 'required',
            'user_type' => 'required',
            'username' => 'required|unique:user',
            'password' => 'required',
        ]);

        if ($request->input('user_type') == 'STUDENT') {
            $request->validate([
                'college_info' => 'bail|required',
                'college_info.name' => 'required',
                'college_info.edu_grade' => 'required',
                'college_info.stream' => 'required',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        if ($request->input('user_type') == 'WORKING_PROFESSIONAL') {
            $request->validate([
                'company_info' => 'bail|required',
                'company_info.name' => 'required',
                'company_info.designation' => 'required',
                'company_info.work_desc' => 'required',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        try {

            $user_id = DB::table('user')->insertGetId([
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'number' => $request->input('number'),
                'age' => $request->input('age'),
                'user_type' => $request->input('user_type'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);


            if ($request->input('user_type') == 'STUDENT') {
                DB::table('college_info')->insert([
                    'user_id' => $user_id,
                    'name' => $request->input('college_info.name'),
                    'edu_grade' => $request->input('college_info.edu_grade'),
                    'stream' => $request->input('college_info.stream'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            if ($request->input('user_type') == 'WORKING_PROFESSIONAL') {
                DB::table('company_info')->insert([
                    'user_id' => $user_id,
                    'name' => $request->input('company_info.name'),
                    'designation' => $request->input('company_info.designation'),
                    'work_desc' => $request->input('company_info.work_desc'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            return Response([
                'status' => true,
                'code' => Constants::USER_STORED,
                'user_id' => $user_id
            ]);

        } catch (PDOException $e) {
            GLogger::error(__CLASS__, __FUNCTION__, $e);
            return Response([
                'status' => false,
                'code' => Constants::USER_STORED_FAILED,
                'message' => $e->getMessage()
            ]);
        }

    }

}
