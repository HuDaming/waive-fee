<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;

class PagesController extends Controller
{
    public function index(Request $request)
    {
        if (is_mobile()) {
            return view('pages.h5');
        } else {
            $authCode = $request->input('auth_code', null);
            if (!$authCode) {
                $appId = config('services.alipay.app_id');
                session()->put('alipay_state', md5(uniqid(time(), true)));
                $url = "https://openauth.alipaydev.com/oauth2/publicAppAuthorize.htm?app_id={$appId}&scope=auth_user&redirect_uri=" . urlencode(route('root', ['product_id' => 1])) . "&state=" . session()->get('alipay_state');
                return redirect($url);
            }

            if (session()->get('alipay_state') == $request->input('state')) {
                // 换取授权token
                $accessToken = app('alipay')->getAccessToken($authCode);

                // 查询用户是否已经存在
                $user = User::where('open_id', $accessToken->user_id)->first();

                // 用户不存在，获取用户信息新建用户
                if (!$user) {
                    $userInfo = app('alipay')->getUserInfo($accessToken->access_token);
                    if (!empty($userInfo->code) && $userInfo->code == 10000) {
                        $userData = [];
                        $userData['open_id'] = $userInfo->user_id;
                        $userData['gender'] = $userInfo->gender == 'm' ? 1 : 2;

                        if (!empty($userInfo->nick_name)) {
                            $userData['name'] = $userInfo->nick_name;
                            $userData['nickname'] = $userInfo->nick_name;
                        }

                        if (!empty($userInfo->province)) $userData['province'] = $userInfo->province;
                        if (!empty($userInfo->city)) $userData['city'] = $userInfo->city;
                        if (!empty($userInfo->avatar)) $userData['avatar'] = $userInfo->avatar;

                        $user = User::create($userData);
                    } else {
                        throw new InvalidRequestException($userInfo->sub_msg);
                    }
                }

                // 用户登录
                if ($user) Auth::login($user);
            }

            // 跳转至支付页面
            return redirect()->route('orders.create', [
                'user_id' => $user->id,
                'product_id' => 1,
                'seller_id' => 2,
            ]);
        }
    }
}
